{*

* 2015 VR pay eCommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
*
*  @author VR pay eCommerce <info@vr-epay.info>
*  @copyright  2015 VR pay eCommerce
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if $module == "vrpayecommerce"}
    {if !empty($error_message)}
        {literal}
        <script>
        document.body.onload = addErrorElement;
        
        function addErrorElement () { 
            var errorMessage = "{/literal}{$error_message|escape:'javascript'}{literal}";
            var errorDiv = document.createElement("div");
            errorDiv.setAttribute('class', 'alert alert-danger');
            errorDiv.setAttribute('role', 'alert');
            errorDiv.setAttribute('data-alert', 'danger');
            errorDiv.setAttribute('style', 'text-align: center;');
            var errorContent = document.createTextNode(errorMessage); 
            errorDiv.appendChild(errorContent);

            var sp2 = document.getElementById("content");
            var parentDiv = sp2.parentNode;

            parentDiv.insertBefore(errorDiv, sp2);

        }
        </script>
        {/literal}
    {/if}
{/if}

