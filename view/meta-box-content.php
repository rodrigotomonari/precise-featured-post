<?php

global $post;

wp_nonce_field($this->plugin_slug . '_meta_box_save', $this->plugin_slug . '_meta_box_nonce');

foreach ($this->get_feature_post_options()[$post->post_type] as $section):
    $value = get_post_meta($post->ID, $this->format_meta_key($section['name']), true);
    $field_name = $this->format_field_name($section['name']); ?>
    <p>
        <label for="<?= $field_name ?>"><?= $section['label'] ?></label>
        <select name="<?= $field_name ?>" id="<?= $field_name ?>">
            <option value="0" <?php echo $value == 0 ? 'selected="selected"' : '' ?>>
                <?= __('No', $this->plugin_name) ?>
            </option>
            <option value="1" <?php echo $value > 0 ? 'selected="selected"' : '' ?>>
                <?= __('Yes', $this->plugin_name) ?>
            </option>
        </select>
    </p>
    <?php
endforeach; ?>

