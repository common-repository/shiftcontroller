<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_CheckboxDetails
{
	public function render( $name, $value, $checked, $label, $details = NULL, $inverse = FALSE )
	{
		$myId = 'hc4-input-checkboxdetails-' . HC3_Functions::generateRand(2);

		// $value = $this->helper->getValue( $name, $value );

	// CHECKBOX
		$checkboxView = '';
		$checkboxView .= '<label class="hc-block hc-xs-py1 hc-mr2">';
		// $checkboxView = $this->inputCheckbox->render( $name, $value, $checked, $label );

		$checkboxView .= '<input type="checkbox" class="hc4-input-checkbox" name="hc-' . $name . '" value="' . $value . '"';
		if( $checked ){
			$checkboxView .= ' checked="checked"';
		}
		$checkboxView .= '>';
		if( NULL !== $label ){
			$checkboxView .= $label;
		}
		$checkboxView .= '</label>';

		$detailsView = '<div class="hc4-input-checkboxdetails-detail">' . $details . '</div>';

		$out = array();
		if( ! $inverse ){
			$out[] = $checkboxView;
			$out[] = $detailsView;
		}
		else {
			$out[] = $detailsView;
			$out[] = $checkboxView;
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
var isInverse = <?php echo $inverse ? 'true' : 'false'; ?>;

function CheckboxDetailsInput( el ){
	var ii = 0, jj = 0;
	var togglers = el.getElementsByClassName( 'hc4-input-checkbox' );
	var details = el.getElementsByClassName( 'hc4-input-checkboxdetails-detail' );

	this.render = function(){
		for( ii = 0; ii < togglers.length; ii++ ){
			for( jj = 0; jj < details.length; jj++ ){
				if( togglers[ii].checked ){
					details[jj].style.display = isInverse ? 'none' : 'block';
				}
				else {
					details[jj].style.display = isInverse ? 'block' : 'none';
				}
			}
		}
	};

	for( ii = 0; ii < togglers.length; ii++ ){
		togglers[ii].addEventListener( 'change', this.render );
	}
};

var el = new CheckboxDetailsInput( document.getElementById('<?php echo $myId; ?>') );
el.render();

})();
</script>

<?php 
		return ob_get_clean();
	}
}