<?php

namespace App\Jobs;

use App\Models\MinecraftPlayerSession;
use App\Models\Player;
use App\Models\PlayerPunishment;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePunishmentInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private PlayerPunishment $punishment)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AiService $aiService): void
    {
        // return if already generating or already generated...
        if ($this->punishment->insights && in_array($this->punishment->insights['status'], ['generating', 'generated'])) {
            return;
        }

        // return if ai insights is disabled
        if (!config('minetrax.banwarden.ai_insights_enabled')) {
            return;
        }

        // mark as generating...
        $this->punishment->update([
            'insights' => [
                'status' => 'generating',
            ],
        ]);

        $this->punishment
            ->makeVisible('ip_address')
            ->loadMissing([
                'country:id,name,iso_code',
                'victimPlayer:id,uuid,username,skin_texture_id',
                'creatorPlayer:id,uuid,username,skin_texture_id',
                'removerPlayer:id,uuid,username,skin_texture_id',
            ]);

        // Details
        $punishmentJsonString = json_encode($this->punishment);

        // Last Punishments
        if ($this->punishment->uuid) {
            $lastPunishments = PlayerPunishment::with([
                'country:id,name,iso_code',
                'victimPlayer:id,uuid,username,skin_texture_id',
                'creatorPlayer:id,uuid,username,skin_texture_id',
                'removerPlayer:id,uuid,username,skin_texture_id',
            ])
                ->where('uuid', $this->punishment->uuid)
                ->where('id', '!=', $this->punishment->id)
                ->orderByDesc('start_at')
                ->limit(10)
                ->get()
                ->makeVisible('ip_address');
        } else {
            $lastPunishments = PlayerPunishment::with([
                'country:id,name,iso_code',
                'victimPlayer:id,uuid,username,skin_texture_id',
                'creatorPlayer:id,uuid,username,skin_texture_id',
                'removerPlayer:id,uuid,username,skin_texture_id',
            ])
                ->where('ip_address', 'LIKE', $this->punishment->ip_address)
                ->where('id', '!=', $this->punishment->id)
                ->orderByDesc('start_at')
                ->limit(10)
                ->get()
                ->makeVisible('ip_address');
        }
        $lastPunishmentsJsonString = json_encode($lastPunishments);

        // Last Sessions
        if ($this->punishment->uuid) {
            $pastSessions = MinecraftPlayerSession::with(['country:id,name,iso_code', 'server:id,name'])
                ->where('player_uuid', $this->punishment->uuid)
                ->where('session_started_at', '<=', $this->punishment->start_at)
                ->orderByDesc('session_started_at')
                ->limit(10)
                ->get()
                ->makeVisible('player_ip_address');
        } else {
            $pastSessions = MinecraftPlayerSession::with(['country:id,name,iso_code', 'server:id,name'])
                ->where('player_ip_address', 'LIKE', $this->punishment->ip_address)
                ->where('session_started_at', '<=', $this->punishment->start_at)
                ->orderByDesc('session_started_at')
                ->limit(10)
                ->get()
                ->makeVisible('player_ip_address');
        }
        $lastSessionsJsonString = json_encode($pastSessions);

        // Possible Alts
        if (!$this->punishment->ip_address) {
            $altPlayers = [];
        } else {
            $firstTwoOctets = explode('.', $this->punishment->ip_address);
            $firstTwoOctets = $firstTwoOctets[0] . '.' . $firstTwoOctets[1] . '.%';
            $altUuids = MinecraftPlayerSession::distinct()->select('player_uuid')
                ->where('player_uuid', '!=', $this->punishment->uuid)
                ->where('player_ip_address', 'LIKE', $firstTwoOctets)
                ->pluck('player_uuid');
            $altPlayers = Player::select(['id', 'uuid', 'username', 'skin_texture_id', 'first_seen_at', 'last_seen_at', 'country_id', 'ip_address', 'play_time'])
                ->whereIn('uuid', $altUuids)
                ->with('country:id,name,iso_code')
                ->withCount('punishments')
                ->orderByDesc('last_seen_at')
                ->limit(10)
                ->get()
                ->makeVisible('ip_address');
        }
        $possibleAltsJsonString = json_encode($altPlayers);

        $systemPrompt = view('gptprompts.punishment-insights', [
            'locale' => config('app.locale'),
        ]);
        $question = <<<QUESTION
        Punishment Details:
        $punishmentJsonString

        Last 10 Punishments:
        $lastPunishmentsJsonString

        Last 10 Sessions (before this punishment):
        $lastSessionsJsonString

        Possible Alts (players with similar ip address):
        $possibleAltsJsonString
        QUESTION;

        $response = $aiService->simplePrompt($systemPrompt, $question, 0.3, 1000, [
            'response_format' => [
                'type' => 'json_object',
            ]
        ]);
        $responseData = json_decode($response, true);

        // mark as generated...
        $this->punishment->update([
            'insights' => [
                'status' => 'generated',
                ...$responseData,
            ],
        ]);
    }

    // on failure, set insights to null
    public function failed(): void
    {
        $this->punishment->update([
            'insights' => null,
        ]);
    }
}
