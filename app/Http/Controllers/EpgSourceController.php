<?php

namespace App\Http\Controllers;

use App\Actions\EpgSources\DeleteEpgSourceAction;
use App\Actions\EpgSources\StoreEpgSourceAction;
use App\Actions\EpgSources\UpdateEpgSourceAction;
use App\Http\Requests\StoreEpgSourceRequest;
use App\Http\Requests\UpdateEpgSourceRequest;
use App\Models\EpgChannel;
use App\Models\EpgProgramme;
use App\Models\EpgSource;
use App\Services\Epg\EpgSyncService;
use Illuminate\Http\Request;
use Throwable;

class EpgSourceController extends Controller
{
    public function index()
    {
        return view('epg.sources.index', ['sources' => EpgSource::withCount(['channels', 'programmes'])->paginate(20)]);
    }

    public function create()
    {
        return view('epg.sources.form');
    }

    public function store(StoreEpgSourceRequest $request)
    {
        $source = StoreEpgSourceAction::run($request->validated());

        return redirect()->route('epg.sources.edit', $source)->with('success', 'EPG source created.');
    }

    public function edit(EpgSource $source)
    {
        return view('epg.sources.form', compact('source'));
    }

    public function update(UpdateEpgSourceRequest $request, EpgSource $source)
    {
        UpdateEpgSourceAction::run($source, $request->validated());

        return back()->with('success', 'EPG source updated.');
    }

    public function destroy(EpgSource $source)
    {
        DeleteEpgSourceAction::run($source);

        return redirect()->route('epg.sources.index')->with('success', 'EPG source deleted.');
    }

    public function sync(EpgSource $source, EpgSyncService $sync)
    {
        try {
            $result = $sync->sync($source);

            return back()->with('success', "Imported {$result['channels']} channels and {$result['programmes']} programmes.");
        } catch (Throwable $exception) {
            return back()->withErrors(['sync' => $exception->getMessage()]);
        }
    }

    public function channels(Request $request)
    {
        $query = EpgChannel::with('source')->orderBy('display_name');
        if ($request->filled('source_id')) {
            $query->where('epg_source_id', $request->integer('source_id'));
        }
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('display_name', 'like', '%'.$request->string('q').'%')->orWhere('external_id', 'like', '%'.$request->string('q').'%'));
        }

        return view('epg.channels', ['channels' => $query->paginate(50)->appends($request->query()), 'sources' => EpgSource::orderBy('name')->get()]);
    }

    public function programmes(Request $request)
    {
        $query = EpgProgramme::with('channel.source')->orderByDesc('start_at');
        if ($request->filled('channel_id')) {
            $query->where('epg_channel_id', $request->integer('channel_id'));
        }

        return view('epg.programmes', ['programmes' => $query->paginate(50)->appends($request->query())]);
    }

    public function searchChannels(Request $request)
    {
        $request->validate(['source_id' => ['required', 'integer', 'exists:epg_sources,id'], 'q' => ['nullable', 'string', 'max:100']]);

        return EpgChannel::where('epg_source_id', $request->integer('source_id'))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('display_name', 'like', '%'.$request->string('q').'%')->orWhere('external_id', 'like', '%'.$request->string('q').'%')))
            ->orderBy('display_name')->limit(50)->get(['id', 'external_id', 'display_name']);
    }
}
