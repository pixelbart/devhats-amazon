<div class="wrap">
  <h1>Simple Amazon</h1>

  <form method="post" action="options.php">

    <?php settings_fields( 'devhats-amazon-settings' ); ?>
    <?php do_settings_sections( 'devhats-amazon-settings' ); ?>

    <p><?php echo $desc; ?></p>

    <table class="form-table">

      <?php foreach( $fields as $id => $label ): ?>

      <?php $value = esc_attr(get_option($id)); ?>

      <tr valign="top">
        <th scope="row"><?php echo $label; ?></th>
        <td><input class="regular-text code" type="text" name="<?php echo $id; ?>" value="<?php echo $value; ?>" /></td>
      </tr>

      <?php endforeach; ?>

    </table>

    <?php submit_button(); ?>

  </form>
</div>
