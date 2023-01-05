{*
* @copyright 2007-2021 PrestaShop SA
*}
<div class="block_newsletter col-sm-6">
  <div class="row">
  	 <div class="col-md-6 col-xs-12 tt-content">
		<h1 class="tt-title">{l s='Subscribe Newsletter' d='Shop.Theme.Global'}</h1>
		<div class="tt-desc">{l s='Wants to get latest updates! sign up for free.' d='Shop.Theme.Global'}</div>
	  <div class="col-md-10 col-xs-12 tt-input">
      <form action="{$urls.pages.index}#footer" method="post">
        <div class="row">
          <div class="ttinput_newsletter col-xs-12">
            <input
              class="btn btn-primary float-xs-right hidden-xs-down"
              name="submitNewsletter"
              type="submit"
              value="{l s='Subscribe' d='Shop.Theme.Actions'}"
            >
            <input
              class="btn btn-primary float-xs-right hidden-sm-up"
              name="submitNewsletter"
              type="submit"
              value="{l s='OK' d='Shop.Theme.Actions'}"
            >
            <div class="input-wrapper">
             <input
			  name="email"
			  type="email"
			  value="{$value}"
			  placeholder="{l s='Your email address' d='Shop.Forms.Labels'}"
			  aria-labelledby="block-newsletter-label"
			>
            </div>
            <input type="hidden" name="action" value="0">
            <div class="clearfix"></div>
          </div>
          <div class="col-xs-12">
              {if $conditions}
                <p class="newsletter-desc">{$conditions}</p>
              {/if}
              {if $msg}
                <p class="alert {if $nw_error}alert-danger{else}alert-success{/if}">
                  {$msg}
                </p>
              {/if}
			  {hook h='displayNewsletterRegistration'}
			   {if isset($id_module)}
				 {hook h='displayGDPRConsent' id_module=$id_module}
			   {/if}
          </div>
        </div>
      </form>
    </div>
	</div>
    
  </div>
</div>
