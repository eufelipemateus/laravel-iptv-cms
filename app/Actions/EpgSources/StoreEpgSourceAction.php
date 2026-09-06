<?php

namespace App\Actions\EpgSources;

use App\Models\EpgSource;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreEpgSourceAction
{
    use AsAction;

    public function handle(array $data): EpgSource
    {
        $data['enabled'] = (bool) ($data['enabled'] ?? false);

        return EpgSource::create($data);
    }
}
