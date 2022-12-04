{*
* 2022 zekri.me
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement
*
* @author    zekri.me <saifzekri@gmail.com>
* @copyright 2022 zekri.me
* @license   Commercial license (You can not resell or redistribute this software.)
*
*}

{extends file="helpers/form/form.tpl"}

{block name="script"}
    $(document).ready(function() {
        if($('#module_form')[0].hasAttribute('novalidate')) {
            $('#module_form')[0].removeAttribute('novalidate');
        }
    });
{/block}

    {block name="input"}
        {if $input.name == 'SZPAYMENT_ORDER_STATUS_ID'}
            <div class="form-group">
                <select name="{$input.name}" class="col-lg-8 col-md-8" {if $input.required == '1'} required {/if}>
                    <option {if $input.value == '0'} selected {/if} value="">--- {l s='Please select an order status' mod='sz_payment'} ---</option>
                    {foreach from=OrderState::getOrderStates($input.current_lang) item=orderState}
                        <option {if $orderState['id_order_state'] == $input.value} selected {/if} value="{$orderState['id_order_state']}">{$orderState['name']}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}

