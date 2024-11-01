<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_MultiSet
{
	public function render( $name, array $labels, array $options, $value = NULL )
	{
		$myId = 'hc4-input-multiset-' . HC3_Functions::generateRand(2);

		// $value = $this->helper->getValue( $name, $value );

		$out = array();

	// RADIO SET
		$out[] = '<div>';
		foreach( $labels as $k => $label ){
			$out[] = '<div class="hc-nowrap hc-lg-inline-block hc-lg-mx1">';
			$checked = ( $value == $k ) ? TRUE : FALSE;

		// RADIO
			$out[] = '<label class="hc-block hc-xs-py1 hc-mr2">';
			$out[] = '<input type="radio" class="hc4-input-radio" name="hc-' . $name . '" value="' . $k . '"';
			if( $checked ){
				$out[] = ' checked="checked"';
			}
			$out[] = '>';
			if( NULL !== $label ){
				$out[] = $label;
			}
			$out[] = '</label>';

			$out[] = '</div>';
		}
		$out[] = '</div>';

		foreach( $options as $k => $v ){
			$out[] = '<div class="hc4-input-multiset-detail hc4-input-multiset-detail-' . $k . '">';
			$out[] = $v;
			$out[] = '</div>';
		}

		$out = join( '', $out );
		// $out = $this->helper->afterRender( $name, $out );

		ob_start();
?>

<div id="<?php echo $myId; ?>">
<?php echo $out; ?>
</div>

<script>
( function(){

function MultiSetInput( el ){
	var ii = 0, jj = 0;
	var togglers = el.getElementsByClassName( 'hc4-input-radio' );
	var details = el.getElementsByClassName( 'hc4-input-multiset-detail' );

	this.render = function(){
		for( ii = 0; ii < details.length; ii++ ){
			details[ii].style.display = 'none';
		}

		for( ii = 0; ii < togglers.length; ii++ ){
			if( togglers[ii].checked ){
				var showClass = 'hc4-input-multiset-detail-' + togglers[ii].value;
				var showDetails = el.getElementsByClassName( showClass );
				for( jj = 0; jj < showDetails.length; jj++ ){
					showDetails[jj].style.display = 'block';
				}
			}
		}
	};

	for( ii = 0; ii < togglers.length; ii++ ){
		// togglers[ii].addEventListener( 'RadioStateChange', this.render );
		togglers[ii].addEventListener( 'change', this.render );
	}
};

var el = new MultiSetInput( document.getElementById('<?php echo $myId; ?>') );
el.render();

})();
</script>

<?php 
		return ob_get_clean();
	}
}