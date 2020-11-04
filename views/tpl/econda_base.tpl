[{if $oViewConf->isEcondaActive() }]
    <script type="text/javascript" defer="defer" src="https://d35ojb8dweouoy.cloudfront.net/loader/loader.js" client-key="[{$oViewConf->getEcondaClientKey()}]" container-id="1541"></script>
    [{oxscript include=$oViewConf->getModuleUrl('makaira/connect', 'out/src/scripts/features/econda.js') priority=20 }]
[{/if}]
