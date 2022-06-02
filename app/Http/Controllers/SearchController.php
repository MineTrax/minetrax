<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Searchable\ModelSearchAspect;
use Spatie\Searchable\Search;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $searchString = $request->query('q');

        $searchResults = (new Search())
            ->registerModel(User::class, function (ModelSearchAspect $modelSearchAspect) {
                $modelSearchAspect->addSearchableAttribute('name')->addSearchableAttribute('username')
                    ->with('country');
            })
            ->registerModel(Player::class, function (ModelSearchAspect $modelSearchAspect) {
                $modelSearchAspect->addSearchableAttribute('uuid')->addSearchableAttribute('username')
                    ->with(['country', 'rank']);
            })
            ->limitAspectResults(5)
            ->search($searchString);

        // Filter out the return attributes which are not required.
        $searchResults = $searchResults->map(function ($item) {
            $item->country = ['name' => $item->searchable->country?->name,
                'iso_code' => $item->searchable->country?->iso_code,
                'photo_path' => $item->searchable->country?->photo_path
            ];

            if ($item->type == 'players') {
                $item->rank = ['name' => $item->searchable?->rank?->name,
                    'photo_path' => $item->searchable?->rank?->photo_url
                ];
                $item->rating = $item->searchable?->rating;
                $item->uuid = $item->searchable->uuid;
                $item->avatar_url = $item->searchable->avatar_url;
            }

            if ($item->type == 'users') {
                $item->profile_photo_url = $item->searchable->profile_photo_url;
                $item->username = $item->searchable->username;
            }

            unset($item->searchable);
            return $item;
        });

        return $searchResults->groupByType();
    }
}
