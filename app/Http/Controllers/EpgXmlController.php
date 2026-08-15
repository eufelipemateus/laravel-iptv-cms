<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerChannelsM3URequest;
use App\Models\Channel;
use App\Services\Epg\XmltvGenerator;

class EpgXmlController extends Controller
{
    public function public(XmltvGenerator $generator)
    {
        return response()->stream(fn () => $generator->stream(), 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    public function customer(CustomerChannelsM3URequest $request, XmltvGenerator $generator)
    {
        $customer = $request->customer();
        abort_unless($customer->cdn?->slug === $request->slug(), 404);
        $ids = Channel::getCustomerChannelListM3u8($request->slug(), $customer->id)->pluck('channel_id')->unique()->values()->all();

        return response()->stream(fn () => $generator->stream($ids), 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
