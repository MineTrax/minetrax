<?php

namespace App\Models;

use App\Contracts\Commentator;
use App\Traits\CanCommentTrait;
use Cog\Contracts\Love\Reacterable\Models\Reacterable as ReacterableInterface;
use Cog\Laravel\Love\Reacterable\Models\Traits\Reacterable;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Features;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Searchable\Searchable;

class User extends Authenticatable implements Commentator, MustVerifyEmail, ReacterableInterface, Searchable
{
    use CanCommentTrait;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use Reacterable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'dob',
        'country_id',
        'provider_id',
        'provider_name',
        'email_verified_at',
        'user_setup_status',
        'last_login_at',
        'last_login_ip',
    ];

    // For spatie/laravel-permissions https://github.com/spatie/laravel-permission/issues/1540
    public $guard_name = 'sanctum';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'last_login_ip',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'dob' => 'date',
        'social_links' => 'json',
        'settings' => 'json',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url', 'dob_string', 'gender_string', 'is_staff', 'cover_image_url',
    ];

    protected $with = [
        'roles:id,name,display_name,is_staff,color,web_message_format,weight',
        'stickyBadges:id,name,shortname,sort_order',
    ];

    /**
     * Override default email verification in Trait to check if feature is enabled before sending mail.
     */
    public function sendEmailVerificationNotification()
    {
        // Only send verification email if Feature is enabled.
        if (Features::enabled(Features::emailVerification()) && ! $this->hasVerifiedEmail()) {
            $this->notify(new VerifyEmail);
        }
    }

    public function isImpersonating(): bool
    {
        return session()->has('impersonated_by') == $this->id;
    }

    public function getSearchResult(): \Spatie\Searchable\SearchResult
    {
        $url = route('user.public.get', $this->id);

        return new \Spatie\Searchable\SearchResult(
            $this,
            $this->name,
            $url
        );
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        if (config('auth.random_user_avatars')) {
            return 'https://api.dicebear.com/7.x/pixel-art/svg?seed='.urlencode($this->username);
        }

        return url('/images/default_profile_pic.png');
    }

    /**
     * Get the URL to the user's profile photo.
     */
    public function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function () {
            $settings = $this->settings;
            // If settings is not loaded then try loading it.
            if (! $settings) {
                $settings = $this->refresh()->getOriginal('settings');
            }

            if ($settings && array_key_exists('profile_photo_source', $settings) && $settings['profile_photo_source']) {
                switch ($settings['profile_photo_source']) {
                    case 'gravatar':
                        $email = $this->email;
                        // Note: This fix is because we don't load email in public places like shoutbox etc
                        if (! $email) {
                            $email = $this->refresh()->getOriginal('email');
                        }
                        $hashedEmail = md5($email);

                        return "https://www.gravatar.com/avatar/{$hashedEmail}?size=150&d=mp";
                        // minecraft head using his first linked player username
                    case 'linked_player':
                        if (! $this?->players()?->first()) {
                            break;
                        }
                        $player = $this->players()->first();

                        return route('player.avatar.get', [$player->uuid, $player->username, 'size' => 150]);
                    default:
                        break;
                }
            }

            // If source is null this means he prefer uploading.
            return $this->profile_photo_path
                ? Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
                : $this->defaultProfilePhotoUrl();
        });
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image_path) {
            return Storage::disk($this->profilePhotoDisk())->url($this->cover_image_path);
        }

        return url('/images/default_user_profile_cover.jpg');
    }

    /**
     * Update user cover image
     *
     * @return void
     */
    public function updateCoverImage(UploadedFile $image)
    {
        tap($this->cover_image_path, function ($previous) use ($image) {
            $this->forceFill([
                'cover_image_path' => $image->storePublicly(
                    'cover-images',
                    ['disk' => $this->profilePhotoDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->profilePhotoDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete user cover image
     *
     * @return void
     */
    public function deleteCoverImage()
    {
        Storage::disk($this->profilePhotoDisk())->delete($this->cover_image_path);

        $this->forceFill([
            'cover_image_path' => null,
        ])->save();
    }

    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function sessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function players(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_user')->withTimestamps();
    }

    public function getDobStringAttribute()
    {
        if ($this->dob) {
            if ($this->settings && array_key_exists('show_yob', $this->settings) && $this->settings['show_yob']) {
                return $this->dob->format('jS F Y');
            }

            return $this->dob->format('jS F');
        }

        return null;
    }

    public function getDobStringWithYearAttribute()
    {
        if ($this->dob) {
            return $this->dob->format('jS F Y');
        }

        return null;
    }

    public function getGenderStringAttribute()
    {
        if ($this->settings && array_key_exists('show_gender', $this->settings) && $this->settings['show_gender']) {
            return match ($this->gender) {
                'm' => 'Male',
                'f' => 'Female',
                'o' => 'Others',
                default => null,
            };
        }

        return null;
    }

    public function isStaffMember(): bool
    {
        return $this->roles->contains(fn ($value) => $value->is_staff == true);
    }

    public function getIsStaffAttribute()
    {
        return $this->isStaffMember();
    }

    public function getRoleWebMessageFormatAttribute()
    {
        return $this->roles->sortByDesc([['weight', 'desc']])?->first()?->web_message_format;
    }

    public function getColorAttribute()
    {
        return $this->roles->first()->color;
    }

    /**
     * The options he has vote to
     */
    public function pollOptions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PollOption::class, 'poll_votes')->withTimestamps();
    }

    public function hasVotedForPoll($poll): bool
    {
        return $this->pollOptions()->where('poll_id', $poll->id)->count() !== 0;
    }

    public function voteForPollOption($option)
    {
        // Check if poll is votable
        if (! $option->poll->isVotable()) {
            throw new \Exception('Poll is not votable');
        }

        // Check if user has already voted for poll
        if ($this->hasVotedForPoll($option->poll)) {
            throw new \Exception('User already voted for poll');
        }

        // vote for poll option
        $this->pollOptions()->sync($option->id, false);
        $option->increment('votes', 1);
    }

    public function notificationPreferences()
    {
        $data = [];

        if (! $this?->settings || ! array_key_exists('notifications', $this->settings)) {
            return $data;
        }

        $notificationPreferences = $this?->settings['notifications'];
        if ($notificationPreferences) {
            $data = $notificationPreferences;
        }

        return $data;
    }

    public function badges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->orderBy('sort_order')->withTimestamps();
    }

    public function stickyBadges()
    {
        return $this->morphToMany(Badge::class, 'badgeable')->orderBy('sort_order')->where('is_sticky', true)->withTimestamps();
    }

    public function shouts()
    {
        return $this->hasMany(Shout::class);
    }

    public function customFormSubmissions()
    {
        return $this->hasMany(CustomFormSubmission::class);
    }

    public function maxRoleWeight()
    {
        return $this->roles->sortByDesc([['weight', 'desc']])?->first()?->weight ?? null;
    }
}
