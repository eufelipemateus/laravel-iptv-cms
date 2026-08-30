<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerChannelsM3URequest;
use App\Models\Channel;
use App\Services\Epg\EpgHttpCache;
use App\Services\Epg\XmltvGenerator;
use Illuminate\Http\Request;

class EpgXmlController extends Controller
{
    public function public(Request $request, XmltvGenerator $generator, EpgHttpCache $cache)
    {
        $response = response()->stream(fn () => $generator->stream(), 200, ['Content-Type' => 'application/xml; charset=utf-8']);

        return $cache->apply($request, $response);
    }

    public function customer(CustomerChannelsM3URequest $request, XmltvGenerator $generator, EpgHttpCache $cache)
    {
        $customer = $request->customer();
        abort_unless($customer->cdn?->slug === $request->slug(), 404);
        $ids = Channel::getCustomerChannelListM3u8($request->slug(), $customer->id)->pluck('channel_id')->unique()->values()->all();

        $response = response()->stream(fn () => $generator->stream($ids), 200, ['Content-Type' => 'application/xml; charset=utf-8']);

        return $cache->apply($request, $response, $ids);
    }
}
