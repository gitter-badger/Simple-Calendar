<?php
/**
 * Date Picker Field
 *
 * @package SimpleCalendar/Admin
 */
namespace SimpleCalendar\Admin\Fields;

use SimpleCalendar\Abstracts\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date Picker input field.
 *
 * A special field to choose dates or date ranges.
 * Holds a date value in 'yy-mm-dd' format.
 */
class Date_Picker extends Field {

	/**
	 * Select a date range.
	 *
	 * @access public
	 * @var bool
	 */
	public $range = false;

	/**
	 * Use an inline picker.
	 *
	 * @access public
	 * @var bool
	 */
	public $inline = true;

	/**
	 * Construct.
	 *
	 * @param array $field
	 */
	public function __construct( $field ) {

		$this->range  = isset( $field['range'] )  ? ( $field['range']  === true ? true : false ) : false;
		$this->inline = isset( $field['inline'] ) ? ( $field['inline'] === true ? true : false ) : true;

		$subtype = $this->range === true ? 'simcal-field-date-picker-range ' : '';
		$this->type_class = 'simcal-field-date-picker ' . $subtype;

		$data = array(
			'data-inline' => $this->inline === true ? 'true' : 'false',
		);
		$field['attributes'] = isset( $field['attributes'] ) ? array_merge( $field['attributes'], $data ) : $data;
		parent::__construct( $field );
	}

	/**
	 * Output the field markup
	 */
	public function html() {

		echo $this->description ? '<p class="description">' . wp_kses_post( $this->description ) . '</p>' : '';

		?>
		<div id="<?php echo $this->id; ?>"
		     class="<?php echo $this->class; ?>"
		     <?php echo $this->style ? 'style="' . $this->style . '"' : ''; ?>
			 <?php echo $this->attributes ?>>

			<?php if ( false === $this->range ) : ?>

				<i class="simcal-icon-calendar"></i>
				<input type="<?php echo $this->inline === true ? 'text' : 'hidden'; ?>"
				       name="<?php echo $this->name; ?>"
				       value="<?php echo $this->value; ?>"
				       placeholder="..."
				       readonly="readonly" />
				<?php echo $this->inline === true ? $this->tooltip : ''; ?>

			<?php else: ?>

				<?php // @todo when a date range picker will be needed, this can be extended ?>

			<?php endif; ?>

		</div>
		<?php

	}

}
