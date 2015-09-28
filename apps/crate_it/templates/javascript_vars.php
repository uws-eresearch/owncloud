<!-- workaround to make var avalaide to javascript -->
<div id="hidden_vars" hidden="hidden">
    <span id="description_length"><?php p($_['description_length']) ?></span>
    <span id="max_sword_mb"><?php p($_['max_sword_mb']) ?></span>
    <span id="publish_warning_mb"><?php p($_['publish_warning_mb']) ?></span>
    <span id="max_zip_mb"><?php p($_['max_zip_mb']) ?></span>
    <span id="selected_crate"><?php p($_['selected_crate']) ?></span>
    <span id="sword_enabled"><?php
        $swordEnabled = false;
        $publish_endpoints = ($_['publish endpoints']);
        $sword_endpoints = $publish_endpoints['sword'];
        foreach ($sword_endpoints as $sword_endpoint) {
            if ($sword_endpoint['enabled'] == true) {
                $swordEnabled = true;
            }
        }
        p($swordEnabled ? 'true' : 'false');?>
    </span>
</div>
