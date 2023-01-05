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

<script type="text/javascript">$(window).load(function(){
     $("#myModal").modal("show");
 });</script>
 <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{if {l s='VRPAYECOMMERCE_TT_TERMS' mod='vrpayecommerce'} == "VRPAYECOMMERCE_TT_TERMS"}Terms and conditions{else}{l s='VRPAYECOMMERCE_TT_TERMS' mod='vrpayecommerce'}{/if}</h4>
        </div>
        <div class="modal-body">
        <div class="alert alert-warning">
      

            <p>{if {l s='VRPAYECOMMERCE_TT_VERSIONTRACKER' mod='vrpayecommerce'} == "VRPAYECOMMERCE_TT_VERSIONTRACKER"}By enabling this plugin, you accept to share your IP, email address, etc with Cardprocess. If you wish not to share those information, you can change the setting via .{else}{l s='VRPAYECOMMERCE_TT_VERSIONTRACKER' mod='vrpayecommerce'}{/if}<a href="#" data-dismiss="modal">Admin</a></p>
    </div>
          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>