<div class="themes-php">
<div class="wrap">

<?php include('tabs.php'); ?>

<div id="customsidebarspage">
<div id="poststuff">


<div id="col-right">

	<h2 class="title"><?php _e('New Sidebar', CSB_LANG ); ?></h2>
	<p><?php _e('When a custom sidebar is created, it is shown in the widgets page. There you will be able to configure it.',  CSB_LANG ); ?></p>
	<form action="themes.php?page=customsidebars" method="post">
		<?php wp_nonce_field( 'custom-sidebars-new');?>
		<div id="namediv" class="stuffbox">
			<h3><label for="sidebar_name"><?php _e('Name', CSB_LANG ); ?></label></h3>
			<div class="inside">
				<input type="text" name="sidebar_name" size="30" tabindex="1" value="" id="link_name" />
			    <p><?php _e('The name has to be unique.', CSB_LANG )?></p>
			</div>
		</div>

		<div id="addressdiv" class="stuffbox">
			<h3><label for="sidebar_description"><?php echo _e('Description', CSB_LANG ); ?></label></h3>
			<div class="inside">
				<input type="text" name="sidebar_description" size="30" class="code" tabindex="1" value="" id="link_url" />
			</div>
		</div>

		<p class="submit"><input type="submit" class="button-primary" name="create-sidebars" value="<?php _e('Create Sidebar', CSB_LANG ); ?>" /></p>
	</form>

</div>




<div id="col-left">

	<form action="themes.php?page=customsidebars" method="post">
		<?php wp_nonce_field( 'custom-sidebars-options','options_wpnonce');?>

		<div id="modifiable-sidebars">
			<h2><?php _e('Replaceable Sidebars', CSB_LANG ); ?></h2>
			<p><?php _e('Select here the sidebars available for replacing. They will appear for replace when a post or page is edited or created. They will be also available in the default sidebars page. You can select several bars holding the SHIFT key when clicking on them.', CSB_LANG ); ?></p>
			<div id="msidebardiv" class="stuffbox">
				<h3><label for="sidebar_name"><?php _e('Select the boxes available for substitution', CSB_LANG ); ?></label></h3>
				<div class="inside">
					<select name="modifiable[]" multiple="multiple" size="5" style="height:auto;">
					<?php foreach($themesidebars as $key => $ts):?>
						<option value="<?php echo $key;?>" <?php echo (!empty($modifiable) && array_search($key, $modifiable)!== FALSE) ? 'selected="selected"' : ''; ?>>
						<?php echo $ts['name'];?>
						</option>
					<?php endforeach;?>
					</select>
				</div>
			</div>
		</div>
		<input type="hidden" id="_wpnonce" name="_wpnonce" value="0a6b5c3eae" />
		<input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/themes.php?page=customsidebars" /><p class="submit"><input type="submit" class="button-primary" name="update-modifiable" value="<?php _e('Save Changes', CSB_LANG ); ?>" /></p>

	</form>
</div>






<?php
////////////////////////////////////////////////////////
//SIDEBARLIST
////////////////////////////////////////////////////////////
?>

<div id="sidebarslistdiv">
	<script type="text/javascript">
		jQuery(document).ready( function($){
			$('.csdeletelink').click(function(){
				return confirm('<?php _e('Are you sure to delete this sidebar?', CSB_LANG );?>');
			});
		});
	</script>
	<h2><?php _e('All the Custom Sidebars', CSB_LANG ); ?></h2>
	<p><?php _e('If a sidebar is deleted and is currently on use, the posts and pages which uses it will show the default sidebar instead.', CSB_LANG ); ?></p>
	<table class="widefat fixed" cellspacing="0">

	<thead>
		<tr class="thead">
			<th scope="col" id="name" class="manage-column column-name" style=""><?php _e('Name', CSB_LANG ); ?></th>
			<th scope="col" id="email" class="manage-column column-email" style=""><?php _e('Description', CSB_LANG ); ?></th>
			<th scope="col" id="config" class="manage-column column-date" style=""></th>
			<th scope="col" id="edit" class="manage-column column-rating" style=""></th>
			<th scope="col" id="delete" class="manage-column column-rating" style=""></th>
		</tr>
	</thead>


	<tbody id="custom-sidebars" class="list:user user-list">

		<?php if(sizeof($customsidebars)>0): foreach($customsidebars as $cs):?>
		<tr id="cs-1" class="alternate">
			<td class="name column-name"><?php echo $cs['name']?></td>
			<td class="email column-email"><?php echo $cs['description']?></td>
			<td class="role column-date"><a class="" href="widgets.php"><?php _e('Configure Widgets', CSB_LANG ); ?></a></td>
			<td class="role column-rating"><a class="" href="themes.php?page=customsidebars&p=edit&id=<?php echo $cs['id']; ?>"><?php _e('Edit', CSB_LANG ); ?></a></td>
			<td class="role column-rating"><a class="csdeletelink" href="themes.php?page=customsidebars&delete=<?php echo $cs['id']; ?>&_n=<?php echo $deletenonce; ?>"><?php _e('Delete', CSB_LANG ); ?></a></td>
		</tr>
		<?php endforeach;else:?>
		<tr id="cs-1" class="alternate">
			<td colspan="3"><?php _e('There are no custom sidebars available. You can create a new one using the left form.', CSB_LANG ); ?></td>
		</tr>
		<?php endif;?>

	</tbody>

	</table>
</div>





<?php
////////////////////////////////////////////////////////
//RESET SIDEBARS
////////////////////////////////////////////////////////////
?>
<div id="resetsidebarsdiv">
	<form action="themes.php?page=customsidebars" method="post">
	<input type="hidden" name="reset-n" value="<?php echo $deletenonce; ?>" />
	<h2><?php _e('Reset Sidebars', CSB_LANG ); ?></h2>
	<p><?php _e('Click on the button below to delete all the Custom Sidebars data from the database. Keep in mind that once the button is clicked you will have to create new sidebars and customize them to restore your current sidebars configuration.</p><p>If you are going to uninstall the plugin permanently, you should use this button before, so there will be no track about the plugin left in the database.', CSB_LANG ); ?></p>

	<p class="submit"><input onclick="return confirm('<?php _e('Are you sure to reset the sidebars?', CSB_LANG ); ?>')"type="submit" class="button-primary" name="reset-sidebars" value="<?php _e('Reset Sidebars', CSB_LANG ); ?>" /></p>

	</form>
</div>

<?php include('footer.php'); ?>


</div>
</div>

</div>
</div>
