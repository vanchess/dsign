<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\DnContract;

class CreateDnContractService {

    public function createDnContract(int $msgId, string $name, int $moOrganizationId, string $ogrn) {
        $contract = new DnContract();
        $contract->msg_id = $msgId;
        $contract->name = $name;
        $contract->mo_organization_id = $moOrganizationId;
        $contract->ogrn = $ogrn;
        $contract->effective_from = now();
        $contract->save();
    }

}
