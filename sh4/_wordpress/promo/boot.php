<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Promo_Boot
{
	public function __construct(
		HC3_Ui_Topmenu $topmenu,
		HC3_Hooks $hooks,
		HC3_Auth $auth,
		HC3_IPermission $permission
	)
	{
		if( defined('SH4_PRO_VERSION') && (! defined('SH4_FORCE_PROMO')) ){
			return;
		}

	// promo
		$label = 'ShiftController Pro';
		$href = 'https://www.shiftcontroller.com/order/';

		// $hooks
		// 	->add( 'sh4/app/html/view/admin::menu::after', function( $return ) use ($label, $href) {
		// 		$label = '<div class="hc-border hc-border-olive hc-rounded hc-p2">' . $label . '</div>';
		// 		$return['promo'] = array( $href, $label );
		// 		return $return;
		// 		})
		// 	;

		$currentUser = $auth->getCurrentUser();
		$isAdmin = $permission->isAdmin($currentUser);

		if( $isAdmin ){
			$topmenu
				->addAfter( 'profile', 'promo', array( $href, $label) )
				;
		}

		$hooks
			->add( 'hc3/layout::render::before', function( $args ) use($label, $href, $isAdmin) {
				if( ! ($isAdmin && is_admin()) ){
					return $args;
				}

				$content = $args[0];
	
				$promo = array();
				if( is_admin() ){
					// $promo[] = '<div class="update-nag hc-block hc-fs4 hc-my3">';
					$promo[] = '<div class="notice notice-success inline hc-block hc-fs4 hc-my3 hc-p3">';
				}
				else {
					$promo[] = '<div class="hc-border hc-border-olive hc-rounded hc-p3 hc-block hc-fs4">';
				}
				$promo[] = '<span class="dashicons dashicons-star-filled hc-olive"></span> <a target="_blank" href="' . $href . '"><strong>' . $label . '</strong></a> with nice features like recurring shifts, bulk actions, shifts pickup and more!';
				$promo[] = '</div>';
				$promo = join('', $promo);

				$content = $promo . $content;
				$args[0] = $content;
				return $args;
				})
			;
	}
}