<?php

namespace App\Http\Controllers;

use App\Events\ShoutCreated;
use App\Models\Shout;
use Illuminate\Http\Request;

class ShoutController extends Controller
{
    public function index()
    {
        $shouts = Shout::with('user:id,username,name,profile_photo_path,settings')->latest()->limit(5)->get();
        return $shouts;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Shout::class);

        $request->validate([
            'message' => 'required|max:200'
        ]);

        $shout = Shout::create([
            'message' => $request->message,
            'user_id' => $request->user()->id
        ]);
        $shout->load('user:id,username,name,profile_photo_path,settings');

        broadcast(new ShoutCreated($shout))->toOthers();

        return response($shout);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Shout $shout
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Shout $shout): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $shout);

        $shout->delete();
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => 'Deleted Successfully', 'body' => 'Shout Deleted Successfully']]);
    }
}
