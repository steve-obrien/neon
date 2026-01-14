{*
 * receives data:
 * - fields => array of field objects
 * - csrf_token => the cross site request forgery token string
 * - form => the form object
 * protectHtmlEntities:
 * - When the data is passed through as a string to HTML, the browser will convert any html entities
 *   to the actual html symbol so e.g &lt;div&gt; becomes <div> before vue receives it. This method
 *   protects this by changing &xx; to &amp;xx; so these are converted back to &xx; by the browser
 * - An alternative is to pass the JSON through via <script> tags where it won't be interpreted.
 *}
<neon-core-form-form v-bind='{protectHtmlEntities input=$form->toJson()}' id="{$form->id}"></neon-core-form-form>
