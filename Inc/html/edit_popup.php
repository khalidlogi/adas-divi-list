<?php
printf(
	'<div id="edit-popup" class="edit-popup draggable" style="display: none;">
<div class="popup-content">
    <button class="dismiss-btn"><i class="fas fa-times"></i></button>
    <h1 class="myh1">%s</h1>
    <form id="edit-form" class="edit-form input-row">
        <button id="submit-button">%s</button>
        <div id="result"></div>
        <!-- Form fields go here -->
    </form>
    <button type="submit" data-nonceupdate="%s" data-form-id="%s"  class="update-btn">%s</button>
</div>
</div>',
	esc_html__( 'Edit values', 'adasdividb' ),
	esc_html__( 'Submit', 'adasdividb' ),
	esc_attr( wp_create_nonce( 'nonceupdate' ) ),
	esc_attr( $this->formbyid ),
	esc_html__( 'Save', 'adasdividb' )
);
