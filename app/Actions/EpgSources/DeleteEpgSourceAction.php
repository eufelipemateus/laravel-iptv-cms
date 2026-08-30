<?php

namespace App\Actions\EpgSources;

use App\Models\EpgSource;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEpgSourceAction
{
    use AsAction;

    public function handle(EpgSource $source): void
    {
        $source->delete();
    }
}
