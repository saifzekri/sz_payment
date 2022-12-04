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

{extends file='page.tpl'}

{block name="page_content"}
    <h2>{l s='Unauthorized' mod='sz_payment'}</h2>
    <p class="alert alert-danger">
        {l s='You are not authorized to use this feature' mod='sz_payment'}
    </p>
{/block}
