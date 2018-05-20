<?php

/** Returns HTML safe value */
function form_escape($value) {
    return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
}

/** Returns HTML safe attributes */
function form_escape_attr($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'utf-8');
}

/** Renders HTML attributes from array */
function form_render_attributes($attributes) {
    $attribute_html = [];

    foreach ($attributes as $key => $value){
        if (is_array($value))
            $value = implode(' ', $value);

        if (is_int($key))
            $attribute_html[] = form_escape_attr($value);
        else
            $attribute_html[] = sprintf('%s="%s"', $key, form_escape_attr($value));
    }

    return implode(' ', $attribute_html);
}

/**
 * Form: A generic class to render and validate an HTML form
 */
class Form
{
    protected $name;
    protected $fields = [];

    public function __construct($name, array $fields=[]) {
        $this->name = $name;
        $this->add_fields($fields);
        $this->initialize();
    }

    /** Initializes fields with their data */
    public function initialize() {
        foreach ($this->fields as $field)
            $field->initialize();
    }

    /** Returns true if the form has been submitted */
    public function is_submitted() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /** Returns true if form is submitted and all fields are validated */
    public function validate() {
        if (!$this->is_submitted())
            return false;

        $result = true;
        foreach ($this->fields as $field)
            $result = $field->validate() && $result;

        return $result;
    }

    /** Returns HTML string of the form */
    public function render(array $attributes=[], $action=null) {
        $attributes['id'] = $this->name;
        $attributes['method'] = 'POST';

        if(!empty($action))
            $attributes['action'] = $action;

        return sprintf('<form %s>%s %s</form>',
            form_render_attributes($attributes),
            $this->render_body(),
            $this->render_buttons()
        );
    }
    
    /** Returns HTML string of a field by key, with label and errors in a container element */
    public function render_field($key, array $attributes=[], array $error_attributes=[], array $parent_attributes=[]) {
        return $this->_render_field($this->fields[$key], $attributes, $error_attributes, $parent_attributes);
    }

    /** Returns HTML string of the form body */
    protected function render_body() {
        $body_html = [];
        
        foreach ($this->fields as $field)
            $body_html[] = $this->_render_field($field);


        return implode(' ', $body_html);
    }

    /** Returns HTML string of the form buttons*/
    protected function render_buttons() {
        return '<button type="submit">Submit</button>';
    }

    /** Returns HTML string of a field, with label and errors in a container element */
    protected function _render_field($field, array $attributes=[], array $error_attributes=[], array $parent_attributes=[]) {
        if (get_class($field) === 'CheckBoxField')
            return sprintf('<div %s>%s %s</div>', 
                form_render_attributes($parent_attributes),
                $field->render_with_label($attributes),
                $this->render_field_errors($field, $error_attributes)
            );
        return sprintf('<div %s>%s %s %s</div>', 
            form_render_attributes($parent_attributes),
            $field->render_label(),
            $field->render($attributes),
            $this->render_field_errors($field, $error_attributes)
        );
    }
    
    /** Returns HTML string with errors of a field */
    protected function render_field_errors($field, $attributes) {
        $error_html = [];
        $errors = array_unique($field->errors);
        foreach ($errors as $error) {
            $error_html[] = sprintf('<span %s>%s</span>', 
                form_render_attributes($attributes),
                form_escape($error));
        }
        return implode(' ', $error_html);
    }

    /** Add a fields */
    public function add_field($field_name, $field) {
        $field->set_name($field_name);
        $field->set_form($this);
        $this->fields[$field_name] = $field;
    }

    /** Adds multiple fields from field_name => field pairs*/
    public function add_fields($fields) {
        foreach ($fields as $field_name => $field)
            $this->add_field($field_name, $field);
    }

    /** Delete a field */
    public function delete_field($field_name) {
        unset($this->fields[$field_name]);
    }

    /** Returns a field */
    public function get_field($field_name) {
        return $this->fields[$field_name];
    }

    /** Returns list of fieldname => field pairs */
    public function get_fields() {
        return $this->fields;
    }

    /** Returns list of fieldname => field pairs */
    public function get_name() {
        return $this->name;
    }

    /** Returns the value of a field */
    public function get_value($field_name) {
        return $this->fields[$field_name]->value;
    }

    /** Returns list of fieldname => value pairs */
    public function get_values() {
        $values = [];

        foreach ($this->fields as $field_name => $field)
            $values[$field_name] = $field->value;

        return $values;
    }

    /** Updates the value of a field */
    public function set_value($field_name, $value) {
        $this->fields[$field_name]->value = $value;
    }
    
    /** Updates the values of multiple fields */
    public function set_values($values) {
        foreach ($values as $field => $value) {
            if (isset($this->fields[$field]))
                $this->fields[$field]->value = $value;
        }
    }

    /** 
     * Updates the value of a field, but only if the form has not been submitted.
     */
    public function prefill_value($field_name, $values) {
        if (!$this->is_submitted())
            $this->set_values($field_name, $values);
    }

    /** 
     * Updates the value of multiple fields, but only if the form has not been 
     * submitted.
     */
    public function prefill_values($values) {
        if (!$this->is_submitted())
            $this->set_values($values);
    }
}


/**
 * Field: An abstract, generic class for a HTML field
 */
abstract class Field
{
    protected $name;
    protected $label;
    protected $form;
    protected $optional;
    protected $attributes;
    public $value;
    public $errors = [];

    public function __construct($label, $optional=false, array $attributes=[], $name='', $form=null) {
        $this->label = $label;
        $this->optional = $optional;
        $this->attributes = $attributes;
        $this->form = $form;
        if (empty($name)) 
            $name = preg_replace('/[^a-z0-9_]/i', '_', strtolower($name));
        $this->set_name($name);
    }

    /** Initializes the field data from POST */
    public function initialize() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$this->name])){
            $this->value = $_POST[$this->name];
        }
    }

    /** 
     * Returns true if field has a value or is optional, 
     * sets error and returns false otherwise 
     */
    public function validate() {
        if ($this->optional || ( isset($this->value) && !empty(trim($this->value)) ) )
            return true;
        $this->errors[] = sprintf('%s is required', $this->label);
        return false;
    }

    /** Returns HTML string of the label of the field */
    public function render_label() {
        return sprintf('<label for="%s">%s</label>', $this->name, $this->label);
    }

    /** Returns HTML string of the field */
    abstract public function render(array $attributes=[]);

    /** Sets the name of the field */
    public function set_name($name) {
        $this->name = $name;
    }

    /** Sets the form the field belongs to */
    public function set_form($form) {
        $this->form = $form;
    }
}


/**
 * InputField: An class for a HTML input field
 */
class InputField extends Field
{
    protected $type;

    public function __construct() {
        $args = func_get_args();
        $this->type = array_shift($args);
        call_user_func_array(['parent', '__construct'], $args);
    }

    /** Returns HTML string of the field */
    public function render(array $attributes=[]) {
        $attributes = array_merge($this->attributes, $attributes);
        $attributes['type'] = $this->type;
        $attributes['name'] = $this->name;
        $attributes['id'] = $this->form->get_name() . '-' . $this->name;

        if (isset($this->value) )
            $attributes['value'] = $this->value;

        return sprintf("<input %s>\n", form_render_attributes($attributes));
    }
}


/**
 * TextAreaField: An class for a HTML textarea field
 */
class TextAreaField extends Field
{
    /** Returns HTML string of the field */
    public function render(array $attributes=[]) {
        $attributes = array_merge($this->attributes, $attributes);
        $attributes['name'] = $this->name;
        $attributes['id'] = $this->form->get_name() . '-' . $this->name;

        $value = isset($this->value) ? $this->value : '';

        return sprintf("<textarea %s>%s</textarea>\n",
            form_render_attributes($attributes),
            form_escape($value));
    }
}

/**
 * CheckBoxField: An class for a HTML input field with type="checkbox"
 */
class CheckBoxField extends Field
{
    /** 
     * Returns true if field has a value or is optional, 
     * sets error and returns false otherwise 
     */
    public function validate() {
        if ($this->optional || !empty($this->value))
            return true;
        $this->errors[] = 'Required field';
        return false;
    }

    /** Returns HTML string of the field and its label */
    public function render_with_label(array $attributes=[]) {
        return sprintf('<label>%s %s</label>', $this->render($attributes), $this->label);
    }

    /** Returns HTML string of the field */
    public function render(array $attributes=[]) {
        $attributes = array_merge($this->attributes, $attributes);
        $attributes['type'] = 'checkbox';
        $attributes['name'] = $this->name;
        $attributes['id'] = $this->form->get_name() . '-' . $this->name;

        if (!empty($this->value))
            $attributes[] = 'checked';

        return sprintf("<input %s>", form_render_attributes($attributes));
    }
}


/**
 * CheckBoxField: An class for a HTML select field
 */
class SelectField extends Field
{   
    protected $options;

    public function __construct($label, $options, $default=null, $optional=false, array $attributes=[], $name='', $form=null) {
        $this->options = $options;
        if (!empty($default))
            $this->value = $default;
        parent::__construct($label, $optional, $attributes, $name, $form);
    }

    /** 
     * Returns true if field has a value that is a valid option or if the field is optional,
     * sets error and returns false otherwise 
     */
    public function validate() {
        $value = isset($this->value) ? $this->value : '';
        
        $has_mapped_options = count(array_filter(array_keys($this->options), function($value){
            return !is_int($value);
        }));

        if ($this->optional && $value === '')
            return true;
        else if ($has_mapped_options && array_key_exists($value, $this->options))
            return true;
        else if ( !$has_mapped_options && in_array($value, $this->options))
            return true;

        if ($value === '' )
            $this->errors[] = sprintf('%s is required', $this->label);
        else 
            $this->errors[] = sprintf('Please select one of the available options');

        return false;
    }

    /** Helper function to convert an option to it's proper HTML representation */
    private function render_option($value, $option) {
        if (is_array($option) && !empty($option[1]))
            $option_attributes = $option[1];
        else
            $option_attributes = [];

        if (!is_int($value))
            $option_attributes['value'] = $value;

        if (isset($this->value)){
            if ($this->value == $value)
                $option_attributes[] = 'selected';
            else if ($this->value != $value && in_array('selected', $option_attributes))
                // this value is not selected, remove it.
                $option_attributes = array_diff($option_attributes, ['selected']);
        }

        return sprintf("\t<option %s>%s</option>",
                form_render_attributes($option_attributes),
                form_escape(is_array($option) ? $option[0] : $option));
    }

    /** Returns HTML string of the field */
    public function render(array $attributes=[]) {
        $attributes = array_merge($this->attributes, $attributes);
        $attributes['name'] = $this->name;
        $attributes['id'] = $this->form->get_name() . '-' . $this->name;

        $options_html = [];

        foreach ($this->options as $value => $option)
            $options_html[] = $this->render_option($value, $option);

        return sprintf("<select %s>\n%s</select>\n",
            form_render_attributes($attributes),
            implode("\n", $options_html));
    }

    /** Returns option for name */
    public function get_option($name) {
        return $this->options[$name];
    }

    /** Returns display value of the selected option */
    public function get_selected_display() {
        return $this->get_option($this->value)[0];
    }
}


/**
 * StringField: An class for a HTML input field with type="text"
 * (named StringField instead of TextField to prevent confusion with TextAreaField)
 */
class StringField extends InputField
{
    public function __construct() {
        $args = func_get_args();
        array_unshift($args, 'text');
        call_user_func_array(['parent', '__construct'], $args);
    }
}


/**
 * EmailField: An class for a HTML input field with type="email"
 */
class EmailField extends InputField
{
    public function __construct() {
        $args = func_get_args();
        array_unshift($args, 'email');
        call_user_func_array(['parent', '__construct'], $args);
    }

    /** 
     * Returns true if field has a value that is a valid emailaddress or if the field is optional,
     * sets error and returns false otherwise 
     */
    public function validate() {
        $value = isset($this->value) ? $this->value : '';
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);

        if ($this->optional && $value === '')
            return true;
        else if (filter_var($value, FILTER_VALIDATE_EMAIL))
            return true;

        if ($value === '' )
            $this->errors[] = sprintf('%s is required', $this->label);
        else 
            $this->errors[] = sprintf('Please enter a valid email address');

        return false;
    }
}


/**
 * DateField: An class for a HTML input field with type="date"
 */
class DateField extends InputField
{
    protected $format;

    public function __construct($label, $format, $optional=false, array $attributes=[], $name='', $form=null) {
        $this->format = $format;
        parent::__construct('date', $label, $optional, $attributes, $name, $form);
    }

    /** 
     * Returns true if field has a value that matches the provide date format or if the field is 
     * optional, sets error and returns false otherwise
     */
    public function validate() {
        if ($this->optional && $this->value === '')
            return true;
        else if (date_parse_from_format($this->format, $this->value)['error_count'] === 0)
            return true;

        if ($this->value === '' )
            $this->errors[] = sprintf('%s is required', $this->label);
        else 
            $this->errors[] = sprintf('Please enter a valid date');

        return false;
    }
}


/**
 * Bootstrap3Form: An extention to Form to render forms with Bootstrap 3 formatting
 */
class Bootstrap3Form extends Form
{
    /** Returns a Bootstrap 3 style HTML string of a field */
    protected function _render_field($field, array $attributes=[], array $error_attributes=[], array $parent_attributes=[]) {
        if (!isset($attributes['class']))
            $attributes = array_merge(['class' => []], $attributes);
        elseif (is_string($attributes['class']))
            $attributes['class'] = [$attributes['class']];

        if (!isset($error_attributes['class']))
            $error_attributes = array_merge(['class' => []], $error_attributes);
        elseif (is_string($error_attributes['class']))
            $error_attributes['class'] = [$error_attributes['class']];
        $error_attributes['class'][] = 'help-block';

        if (!isset($parent_attributes['class']))
            $parent_attributes = array_merge(['class' => []], $parent_attributes);
        elseif (is_string($parent_attributes['class']))
            $parent_attributes['class'] = [$parent_attributes['class']];
        
        // Highlight field on error
        if (!empty($field->errors))
            $parent_attributes['class'][] = 'has-error';

        // Render field, have special treatement for checkboxes
        if (get_class($field) === 'CheckBoxField')
            $parent_attributes['class'][] = 'checkbox';
        else {
            $attributes['class'][] = 'form-control';
            $parent_attributes['class'][] = 'form-group';
        }

        return parent::_render_field(
            $field,
            $attributes,
            $error_attributes,
            $parent_attributes
        );
    }

    /** Returns a Bootstrap 3 style HTML string of the form buttons */
    protected function render_buttons() {
        return '<div class="form-group"><button type="submit" class="btn btn-primary">Submit</button></div>';
    }
}
