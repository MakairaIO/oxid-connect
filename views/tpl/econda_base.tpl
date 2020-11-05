[{if $oViewConf->isEcondaActive() }]
    <script type="text/javascript" defer="defer" src="https://d35ojb8dweouoy.cloudfront.net/loader/loader.js" client-key="[{$oViewConf->getEcondaClientKey()}]" container-id="[{$oViewConf->getEcondaContainerId()}]"></script>
    <script type="text/javascript" src="[{$oViewConf->getModuleUrl('makaira/connect', 'out/src/scripts/features/econda.js')}]"></script>
[{/if}]
