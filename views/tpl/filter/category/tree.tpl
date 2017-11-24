[{assign var="items" value=$aggregation->values}]
[{defun name="tree" items=$items}]
<ul class="makaira-filter__list">
    [{foreach from=$items item="item" name="items"}]
        <li class="makaira-filter__item[{if $item->selected}] makaira-filter__item--active[{/if}]">
            <label class="makaira-filter__label">
                <input
                        type="checkbox"
                        name="makairaFilter[[{$aggregation->key}]][]"
                        class="makaira-input makaira-input--checkbox"
                        value="[{$item->key}]"
                        [{if $item->selected}]checked="checked"[{/if}]
                />
                [{$item->title}]
            </label>
            [{if $item->subtree}]
                [{fun name="tree" items=$item->subtree}]
            [{/if}]
        </li>
    [{/foreach}]
</ul>
[{/defun}]

