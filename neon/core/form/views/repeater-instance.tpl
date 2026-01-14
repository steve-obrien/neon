{*
 * recieves data:
 * - fields => array of field objects
 * - csrf_token => the cross site request forgery token string
 * - form => the form object
 *}
<fieldset class="{$styleClass}" id="{$form->getId()}">
    <legend class="control-label ">{$form->label}</legend>
    {foreach $form->fields as $key => $field}
        {$field->run() nofilter}
    {/foreach}
    <div class="form-group" v-pre>
        <label class="control-label from-group__label">edit</label>
        <div>
            <button onclick="return neon.form.addRow('{$parentFormId}');" class="btn btn-default"><i class="fa fa-plus"></i></button>
            <button onclick="return neon.form.removeRow('{$parentFormId}', '{$form->getName()}');" class="btn btn-default"><i class="fa fa-minus"></i></button>
        </div>
    </div>
</fieldset>