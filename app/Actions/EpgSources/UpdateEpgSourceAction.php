<?php

namespace App\Actions\EpgSources;

use App\Models\EpgSource;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateEpgSourceAction
{
    use AsAction;

    public function handle(EpgSource $source, array $data): EpgSource
    {
        $data['enabled'] = (bool) ($data['enabled'] ?? false);
        $source->update($data);

        return $source;
    }
}
