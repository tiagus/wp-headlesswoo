<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wfacp_template_tabs_container clearfix">
	<div class="wfacp_step_actions">
		<div v-for="(step,slug) in steps" v-if="step.active=='yes'" class="wfacp_step_heading">
			<div v-bind:class="'wfacp_template_tabs '+(slug=='single_step'?'wfacp_active_tabs':'')" v-bind:data-slug="slug">{{step.name}}</div>
			<span class="dashicons dashicons-dismiss" v-if="(current_step==slug) && (current_step!='single_step')" v-on:click.prevent="deleteStep(slug)"></span>
		</div>
	</div>
	<div class="wfacp_add_new_step" v-if="current_step!='third_step'">
		<div class="wfacp_step wfacp_button_add wfacp_modal_open" v-on:click="addNewStep()">
			<?php _e( '+Add New Step', 'woofunnels-aero-checkout' ); ?>
		</div>
	</div>
</div>
<div class="single_step_template" v-for="(step,slug) in steps" v-if="step.active=='yes'" v-bind:data-slug="slug" v-bind:style="slug=='single_step'?'':'display:none'">
	<div v-bind:class="'wfacp_sections_holder '+slug">
		<div v-for="(fieldset,f_index) in fieldsets[slug]" class="wfacp_field_container" v-bind:field-index="f_index" v-bind:step-name="slug">
			<div class="wfacp_field_container_head clearfix">
				<div class="wfacp_field_container_heading">
					<h4 v-html="fieldset.name">{{fieldset.name}}</h4>
					<h5>{{fieldset.sub_heading}}</h5>
				</div>
				<div class="wfacp_field_container_action">
					<a href="#" v-on:click.prevent="editSection(slug,f_index)"><span class="dashicons dashicons-edit"></span></a>
					<a href="#" v-on:click.prevent="deleteSection(slug,f_index)"><span class="dashicons dashicons-no"></span></a>
				</div>
			</div>

			<div v-bind:class="'template_field_container '+slug" v-bind:field-index="f_index" v-on:drop="drop($event,slug,f_index)" v-on:dragover="allowDrop($event)" v-on:dragenter="dragEnter($event)" v-on:dragleave="dragLeave($event)" v-bind:step-name="slug">
				<div v-if="wfacp.tools.ol(fieldset.fields)>0" v-for="(data,index) in fieldset.fields" v-bind:data-id="data.id" class="wfacp_save_btn_style wfacp_item_drag" v-if="data.label" v-bind:data-input-section="data.field_type" v-on:click="editField(slug,f_index,index,$event)">
					<span class="wfacp_remove_fields dashicons dashicons-no" v-on:click="removeField(slug,data.id,data.field_type,f_index,$event)" v-if="data.id!='payment_method'">X</span>
					<span>{{data.label}}</span>
				</div>
				<div v-if="wfacp.tools.ol(fieldset.fields)==0" class="template_field_placeholder_tbl">
					<div class="template_field_placeholder_tbl_cel"><?php _e( 'Drag new fields here to populate the section', 'woofunnels-aero-checkout' ); ?></div>
				</div>
			</div>
		</div>
	</div>
	<div class="wfacp_input_fields_btn">
		<p v-if="wfacp.tools.ol(fieldsets[slug])==0"><?php _e( 'Create a new section to add fields in this step.', 'woofunnels-aero-checkout' ); ?></p>
		<button href="#" class="button" v-on:click="addSection(slug)"><?php _e( 'Add Section', 'woofunnels-aero-checkout' ); ?></button>
	</div>
</div>
