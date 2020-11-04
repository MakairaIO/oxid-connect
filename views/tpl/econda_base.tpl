[{if $oViewConf->isEcondaActive() }]
    <script type="text/javascript" defer="defer" src="https://d35ojb8dweouoy.cloudfront.net/loader/loader.js" client-key="00002a00-efc4a74a-b7e4-31b2-ace2-56d2e5f9a5e6" container-id="1541"></script>
    [{oxscript include=$oViewConf->getModuleUrl('makaira/connect', 'out/src/scripts/features/econda.js') priority=20 }]
[{/if}]
