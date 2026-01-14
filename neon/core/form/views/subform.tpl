{*
 * recieves data:
 * - fields => array of field objects
 * - csrf_token => the cross site request forgery token string
 * - form => the form object
 *}
<fieldset id="{$form->getId()}">
    <legend class="control-label ">{$form->label}</legend>
    {foreach $form->fields as $key => $field}
        <label>{$field->label}</label>
        {$field->print() nofilter}
    {/foreach}
</fieldset>