<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Server;
use App\Settings\PlayerSettings;
use App\Utils\PlayerRating\PlayerRatingCalculator;
use App\Utils\PlayerRating\PlayerScoreCalculator;
use Cache;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlayerSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(PlayerSettings $settings): \Inertia\Response
    {
        return Inertia::render('Admin/Setting/PlayerSetting', [
            'settings' => $settings->toArray(),
            'variables_for_score_static' => config('constants.variables_for_score_static'),
            'variables_for_score_dynamic' => config('constants.variables_for_score_dynamic'),
            'variables_for_rating_static' => config('constants.variables_for_rating_static'),
            'variables_for_rating_dynamic' => config('constants.variables_for_rating_dynamic'),
            'math_functions_for_rating' => config('constants.math_functions_for_rating_and_score'),
        ]);
    }

    public function update(Request $request, PlayerSettings $settings): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'last_seen_day_for_active' => 'required|integer|min:-1|max:365',
            'is_custom_rating_enabled' => 'required|boolean',
            'custom_rating_expression' => 'required_if:is_custom_rating_enabled,true',
            'is_custom_score_enabled' => 'required|boolean',
            'custom_score_expression' => 'required_if:is_custom_score_enabled,true',
            'show_player_intel_to' => 'required|in:none,staff,self,login,all',
        ]);

        // If changes to score algorithm, force run CalculatePlayersJob for all players on next schedule
        if ($settings->is_custom_score_enabled != $request->is_custom_score_enabled || $settings->custom_score_expression != $request->custom_score_expression) {
            Cache::put('CalculatePlayersJob::forceRunForAllPlayers', true);
        }

        // Validate the expression before saving if is_custom_rating_enabled is true
        $serversIds = Server::get()->pluck('id')->toArray();
        if ($request->is_custom_rating_enabled) {
            try {
                $player = Player::with('minecraftPlayers')->first();
                $playerRatingCalculator = new PlayerRatingCalculator();
                $playerRatingCalculator->calculate($request->custom_rating_expression, $player, $serversIds);
            } catch (\Exception $e) {
                return back()->withErrors(['custom_rating_expression' => __('Something wrong with your custom rating expression. Try validating it again.')]);
            }
            $settings->custom_rating_expression = $request->custom_rating_expression;
        }

        if ($request->is_custom_score_enabled) {
            try {
                $player = Player::with('minecraftPlayers')->first();
                $playerScoreCalculator = new PlayerScoreCalculator();
                $playerScoreCalculator->calculate($request->custom_score_expression, $player, $serversIds);
            } catch (\Exception $e) {
                return back()->withErrors(['custom_rating_expression' => __('Something wrong with your custom score expression. Try validating it again.')]);
            }
            $settings->custom_score_expression = $request->custom_score_expression;
        }

        $settings->is_custom_score_enabled = $request->is_custom_score_enabled;
        $settings->is_custom_rating_enabled = $request->is_custom_rating_enabled;
        $settings->last_seen_day_for_active = $request->last_seen_day_for_active;
        $settings->show_player_intel_to = $request->show_player_intel_to;
        $settings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Player Settings Updated Successfully')]]);
    }

    public function validateRatingExpression(Request $request, PlayerSettings $settings): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'custom_rating_expression' => 'required',
            'player_username' => 'required|exists:players,username',
        ]);

        try {
            $player = Player::with('minecraftPlayers')->whereUsername($request->player_username)->first();
            $playerRatingCalculator = new PlayerRatingCalculator();
            $serverIds = Server::get()->pluck('id')->toArray();
            $result = $playerRatingCalculator->calculate($request->custom_rating_expression, $player, $serverIds);
        } catch (\NXP\Exception\DivisionByZeroException $e) {
            return response()->json(['message' => __('Divide by 0 not supported.')], 400);
        } catch (\NXP\Exception\UnknownFunctionException $e) {
            return response()->json(['message' => __('Unknown function in your algorithm.')], 400);
        } catch (\NXP\Exception\UnknownOperatorException $e) {
            return response()->json(['message' => __('Unknown operator in your algorithm.')], 400);
        } catch (\NXP\Exception\UnknownVariableException $e) {
            return response()->json(['message' => __('Unknown variable in your algorithm.')], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => __('Something went wrong.')], 500);
        }

        return response()->json(['rating' => strval($result)]);
    }

    public function validateScoreExpression(Request $request, PlayerSettings $settings): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'custom_score_expression' => 'required',
            'player_username' => 'required|exists:players,username',
        ]);

        try {
            $player = Player::with('minecraftPlayers')->whereUsername($request->player_username)->first();
            $playerScoreCalculator = new PlayerScoreCalculator();
            $serverIds = Server::get()->pluck('id')->toArray();
            $result = $playerScoreCalculator->calculate($request->custom_score_expression, $player, $serverIds);
        } catch (\NXP\Exception\DivisionByZeroException $e) {
            return response()->json(['message' => __('Divide by 0 not supported.')], 400);
        } catch (\NXP\Exception\UnknownFunctionException $e) {
            return response()->json(['message' => __('Unknown function in your algorithm.')], 400);
        } catch (\NXP\Exception\UnknownOperatorException $e) {
            return response()->json(['message' => __('Unknown operator in your algorithm.')], 400);
        } catch (\NXP\Exception\UnknownVariableException $e) {
            return response()->json(['message' => __('Unknown variable in your algorithm.')], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => __('Something went wrong.')], 500);
        }

        return response()->json(['score' => strval($result)]);
    }
}
