<?php
/**
 * Converts Widget options into a Feed URL
 *
 * @param array $widget the Widget options
 * @return string RSS URL
 */
function dda_widget_to_feed($widget){
	$fragments = array();
	//Main search item:
	switch( $widget['who'] ){
		case 'by':
			if( isset($widget['byname']) && !empty($widget['byname']) ){
				$fragments[] = 'by:' . $widget['byname'];
			}
			break;
		case 'favby':
			if( isset($widget['favbyname']) && !empty($widget['favbyname']) ){
				$fragments[] = 'favby:' . $widget['favbyname'];
			}
			break;
		case 'search':
			if( isset($widget['search']) && !empty($widget['search']) ){
				$fragments[] = $widget['search'];
			}
			break;
	}
	//Deal with the Order:
	switch( $widget['order'] ){
		case 'popular-24h':
			$fragments[] = "boost:popular age_sigma:24h";
			break;
		case 'popular-3d':
			$fragments[] = "boost:popular max_age:72h";
			break;
		case 'popular-1w':
			$fragments[] = "boost:popular max_age:168h";
			break;
		case 'popular-1m':
			$fragments[] = "boost:popular max_age:744h";
			break;
		case 'popular':
			$fragments[] = "boost:popular";
			break;
		default:
		case 'time':
			$fragments[] = "sort:time";
			break;
	}
	if( $widget['includescraps'] == '0' ){
		$fragments[] = "-in:scraps";
	}
	//Add all the fragments together as a Search term: (and urlencode it)
	$fragments = array( 'q=' . urlencode(implode(' ',$fragments)) );
	
	switch( $widget['deviationtype'] ){
		case 'prints':
			$fragments[] = 'type=print';
			break;
		default:
		case 'deviations':
			$fragments[] = 'type=deviation';
	}
	//Create Feed:
	return 'http://backend.deviantart.com/rss.xml?'.implode('&',$fragments);
} //end dda_widget_to_feed($widget)

?><style type="text/css">
	.tab-nav{
		display:inline;
		list-style:none;
		margin-right:0;
	}
	.tab-nav li{
		display:inline;
		margin-left: 5px;
		padding: 2px;
		border:1px solid #000000;
		border-bottom: none;
	}
	.tab-nav li:after{
		content:" ";
	}
	a.addNew{
		display:inline;
		margin-left:5px;
		color:#000000;
	}
	a.addNew:hover{
		color:#00FF00;
	}
	#container a,
	#container a:link,
	#container a:visited,
	#container a:hover,
	#container-portfolio a,
	#container-portfolio a:link,
	#container-portfolio a:visited,
	#container-portfolio a:hover{
		color:#000000;
		font-weight:bold;
		border-bottom:none;
	}
	li.activeTab{
		background-color:#999999;
		font-weight:bold;
		color:#FFFFFF;
	}
	.activeTab a{
		color: #FFFFFF;
	}
	.controlbox{
		float:right;
		background-color:#CBCBCB;
	}
	.widgetItem,
	.portfolioItem{
		border: 1px solid #000000;
		background-color:#CCCCCC;
		padding: 3px;
	}
	.subsection {
		border: thun solid #AAA;
		padding: 5px;
		margin-left:10px;
	}
	/*** ***/
	textarea{
		width:98%;
	}
</style>
<script type="text/javascript">
/* <![CDATA[ */
	var $ = jQuery;
	$.tabs = {
		tabContainer: [],
		init: function(HTMLcontainer,container){
			this.tabContainer[container] = HTMLcontainer;
			this.addTabs(container);
			this.hideTabs(container);
			this.openTab(null,container);
		},
		hideTabs: function(container){
			$(this.tabContainer[container] + '>div').hide();
			$(this.tabContainer[container] + '>ul>li').removeClass("activeTab");
		},
		openTab: function(tab,container){
			if( tab == null )
				tab = $(this.tabContainer[container] + '>div:first');

			this.hideTabs(container);
			$(tab).show();
			$(this.tabContainer[container] + '>ul>li>a[@href=#' + $(tab).attr('id') + ']').parent().addClass("activeTab");
		},
		addTabs: function(container){
			$(this.tabContainer[container] + '>ul>li>a').bind('click', {'container': container}, 
			function(event){
				if( this.href.indexOf('#') > -1){
					var tabname = this.href.substr(this.href.indexOf('#'));
					$.tabs.openTab(tabname,event.data.container);
					return false;
				}
			 });
		},
		addTab: function(tab,container){
		$(this.tabContainer[container] + '>ul>li>a[@href=#' + tab + ']').bind('click', {'container': container},
			function(event){
				if( this.href.indexOf('#') > -1){
					var tabname = this.href.substr(this.href.indexOf('#'));
					$.tabs.openTab(tabname,event.data.container);
					return false;
				}
			});
			this.openTab('#' + tab,container);
		},
		removeCurrent: function(container){
			$(this.tabContainer[container] + '>ul>li.activeTab').remove();
		}
	};
/* ]]> */
</script>
<script type="text/javascript">
/* <![CDATA[ */
	function addNewWidget(){
		var WidgetName = "dDeviantArt " + NextWidgetId;
		var WidgetId = WidgetName.replace(' ','-');
		$('#container>ul').append('<li><a href="#' + WidgetId + '" tabindex="1">' + WidgetName + '</a></li>');
		
		var div = $('#Widget-New').clone();
		$(div).html( $(div).html().replace(/\$ID/g,NextWidgetId) );
		$(div).attr("id",WidgetId);
		$(div).attr("class","widgetItem");
		$('#container').append( div );
		$('#Widget-' + NextWidgetId + '-Query').hide();

		$.tabs.addTab(WidgetId,'widget');
		
		NextWidgetId++;
		return false;
	}
	function removeWidget(WidgetId){
		$.tabs.removeCurrent('widget');
		$('#' + WidgetId).remove();
		$.tabs.openTab(null,'widget');
		return false;
	}
	function addNewPortfolioPage(){
		var PortfolioName = "Portfolio " + NextPortfolioId;
		var PortfolioId = PortfolioName.replace(' ','-');
		$('#container-portfolio>ul').append('<li><a href="#' + PortfolioId + '" tabindex="1">' + PortfolioName + '</a></li>');
		
		var div = $('#Portfolio-New').clone();
		$(div).html( $(div).html().replace(/\$ID/g,NextPortfolioId) );
		$(div).attr("id",PortfolioId);
		$(div).attr("class","portfolioItem");
		$('#container-portfolio').append( div );
		$('#Portfolio-' + NextPortfolioId + '-Query').hide();

		$.tabs.addTab(PortfolioId,'portfolio');
		
		NextPortfolioId++;
		
		return false;
	}
	function removePortfolioPage(PortfolioId){
		$.tabs.removeCurrent('portfolio');
		$('#' + PortfolioId).remove();
		$.tabs.openTab(null,'portfolio');
		return false;
	}
	function showPHP(id){
		var name = $('input[@name="widget[' + id + '][name]"]').val();
		alert('PHP Code: <' + '?php dda_widget_embed(\'' + name + '\'); ?' + '>');
		return false;
	}

	$(document).ready(function() {
		$.tabs.init('#container','widget');
		$.tabs.init('#container-portfolio','portfolio');
	});
/* ]]> */
</script>

<noscript>
	<div class="error"><p><?php _e('Sorry, But this page requires Javascript to be enabled'); ?></p></div>
</noscript>

<?php
if( isset($_POST['submit']) ){
	$widgets = array();
	foreach($_POST['widget'] as $key=>$val){
		if( $key === '$ID') //we ignore the templates
			continue;
		//This resets all ID's to 0...n
		$widgets[] = $val;
	}
	
	//Create the final feed:
	for( $i=0; $i<count($widgets); $i++){
		if( isset($widgets[$i]['rss']) && ! empty($widgets[$i]['rss']) ){
			$widgets[$i]['feed'] = $widgets[$i]['rss'];
			continue;
		}
		$widgets[$i]['feed'] = dda_widget_to_feed($widgets[$i]);
	}	
	
	update_option('dda_widgets',$widgets);
	//update_option('dda_widgets',array()); //Clear the Widgets
	echo '<div class="updated"><p>'.__('Widget Options Saved').'</p></div>';
}
$widgets = get_option('dda_widgets');
?>

<div class="wrap">
	<script type="text/javascript">
		var NextWidgetId = <?php echo count($widgets); ?>;
	</script>
	<h2><?php _e('<i>d</i>DeviantArt'); ?></h2>
	<form name="ddeviantart" method="post" action="options-general.php?page=ddeviantart/dda_options.php">
	<p>
		<?php _e('You can add multiple widgets here, Currently they require you to be using a Sidebar widget, its not possible to embed via pure PHP, If theres enough demand for it, the next version will include support for it.'); ?><br/>
		
	</p>
	<div id="container">
		<ul class="tab-nav">
		<?php 
			if( !empty($widgets) ){
				foreach((array)$widgets as $id=>$widget){
					echo '<li><a href="#Widget-'.$id.'" tabindex="1">' . $widget['name']. '</a></li>';
				}
			}
		?>
		</ul>
		<a href="#" onclick="return addNewWidget();" tabindex="1" accesskey="n" class="addNew"><?php _e('[+] New Widget'); ?></a>
		<?php 
			if( !empty($widgets) ){
				foreach((array)$widgets as $id=>$widget){
		?>		
		<div id="Widget-<?php echo $id; ?>" class="widgetItem">
			<script type="text/javascript">
				$(document).ready(function() {
					<?php
						if( 'query' != $widget['type'] )
							echo "$('#Widget-{$widget['id']}-Query').hide();";
					?>
				});
			</script>
			<div class="controlbox">
				<b>-</b> <a href="#remove" onclick="return removeWidget('Widget-<?php echo $id; ?>');"><?php _e('Remove Item'); ?></a><br />
				<b>*</b> <a href="#" onclick="return showPHP('<?php echo $id; ?>');"><?php _e('Show PHP'); ?></a>
			</div>
			<strong><?php _e('Name:'); ?></strong><input type="text" name="widget[<?php echo $id; ?>][name]" value="<?php echo attribute_escape($widget['name']); ?>" /><br />
			<input type="radio" name="widget[<?php echo $id; ?>][type]" value="rss" <?php checked('rss',$widget['type']); ?> onchange="if(this.checked){$('#Widget-<?php echo $id; ?>-Query').hide();}" />
				<strong><?php _e('RSS Feed:'); ?></strong><input type="text" name="widget[<?php echo $id; ?>][rss]" value="<?php echo attribute_escape($widget['rss']); ?>" /><br />
			<input type="radio" name="widget[<?php echo $id; ?>][type]" value="query" <?php checked('query',$widget['type']); ?> onchange="if(this.checked){$('#Widget-<?php echo $id; ?>-Query').show();}" /> <?php _e('Query'); ?><br />
				<div id="Widget-<?php echo $id; ?>-Query" class="subsection">
					<input type="radio" name="widget[<?php echo $id; ?>][who]" id="widget-<?php echo $id; ?>-by" value="by" <?php checked('by',$widget['who']); ?> /> 
						<strong><?php _e('By:'); ?></strong> <input type="text" name="widget[<?php echo $id; ?>][byname]" onclick="$('#widget-<?php echo $id; ?>-by').attr('checked','checked');" value="<?php echo attribute_escape($widget['byname']); ?>" />
					<input type="radio" name="widget[<?php echo $id; ?>][who]" id="widget-<?php echo $id; ?>-favby" value="favby" <?php checked('favby',$widget['who']); ?> />
						<strong><?php _e('FavBy:'); ?></strong> <input type="text" name="widget[<?php echo $id; ?>][favbyname]" onclick="$('#widget-<?php echo $id; ?>-favby').attr('checked','checked');" value="<?php echo attribute_escape($widget['favbyname']); ?>" />
					<input type="radio" name="widget[<?php echo $id; ?>][who]" id="widget-<?php echo $id; ?>-search"  value="search" <?php checked('search',$widget['who']); ?> />
						<strong><?php _e('Search:'); ?></strong> <input type="text" name="widget[<?php echo $id; ?>][search]" onclick="$('#widget-<?php echo $id; ?>-search').attr('checked','checked');" value="<?php echo attribute_escape($widget['search']); ?>" /><br />
					<strong><?php _e('Order By:'); ?></strong> <select name="widget[<?php echo $id; ?>][order]">
						<option value="time"<?php selected('time',$widget['order']); ?>><?php _e('Time'); ?></option>
						<option value="popular-24h"<?php selected('popular-24h',$widget['order']); ?>><?php _e('Popular: 24 Hours'); ?></option>
						<option value="popular-3d"<?php selected('popular-3d',$widget['order']); ?>><?php _e('Popular: 3 Days'); ?></option>
						<option value="popular-1w"<?php selected('popular-1w',$widget['order']); ?>><?php _e('Popular: 1 Week'); ?></option>
						<option value="popular-1m"<?php selected('popular-1m',$widget['order']); ?>><?php _e('Popular: 1 Month'); ?></option>
						<option value="popular"<?php selected('popular',$widget['order']); ?>><?php _e('Popular: All Time'); ?></option>
					</select>
					<?php _e('Random:'); ?> <select name="widget[<?php echo $id; ?>][random]">
								<option value="0"<?php selected('0',$widget['random']);?>><?php _e('No'); ?></option>
								<option value="1"<?php selected('1',$widget['random']);?>><?php _e('Yes'); ?></option>
							</select>
				</div>
				<div id="Widget-<?php echo $id; ?>-Options" class="">
					<?php _e('Type of image to Display:'); ?> <select name="widget[<?php echo $id; ?>][deviationtype]">
													<option value="deviations"<?php selected('deviations',$widget['deviationtype']);?>><?php _e('Deviations'); ?></option>
													<option value="prints"<?php selected('prints',$widget['deviationtype']);?> disabled="disabled"><?php _e('Prints'); ?></option>
												</select>
					<?php _e('Include Scraps?:'); ?> <select name="widget[<?php echo $id; ?>][includescraps]">
											<option value="0"<?php selected('0',$widget['includescraps']);?>><?php _e('No'); ?></option>
											<option value="1"<?php selected('1',$widget['includescraps']);?>><?php _e('Yes'); ?></option>
									</select><br />
					<?php _e('Number of images to show:'); ?>
						<select name="widget[<?php echo $id; ?>][numbertoshow]">
						<?php foreach( range(1,24) as $i )
								echo '<option value="' . $i . '"' . selected($widget['numbertoshow'],$i) . '>' . $i . '</option>'; 
							   ?>
						</select>
					<?php _e('Image Resolution:'); ?>  <select name="widget[<?php echo $id; ?>][size]">
											<option value="100"<?php selected('100',$widget['size']);?> disabled="disabled">100px</option>
											<option value="150"<?php selected('150',$widget['size']);?>>150px</option>
											<option value="300W"<?php selected('300W',$widget['size']);?>>300px</option>
										</select><br />
					<?php _e('Display Orientation:'); ?>
							<select name="widget[<?php echo $id; ?>][orientation]">
								<option value="V"<?php selected('V',$widget['orientation']);?>><?php _e('Vertical'); ?></option>
								<option value="H"<?php selected('H',$widget['orientation']);?> disabled="disabled"><?php _e('Horizontal'); ?></option>
							</select>
					<?php _e('Use inline Styles?'); ?> 
							<select name="widget[<?php echo $id; ?>][inlinestyles]">
								<option value="1"<?php selected('1',$widget['inlinestyles']);?>><?php _e('Yes'); ?></option>
								<option value="0"<?php selected('0',$widget['inlinestyles']);?> disabled="disabled"><?php _e('No'); ?></option>
							</select><br />
					<input type="checkbox" onchange="if(this.checked){ $('#Widget-<?php echo $id; ?>-Options-Advanced').show(); } else { $('#Widget-<?php echo $id; ?>-Options-Advanced').hide(); }" /><?php _e('Show Advanced Options'); ?>
					<div id="Widget-<?php echo $id; ?>-Options-Advanced" style="display:none;">
						<?php _e('HTML:'); ?><br />
						<textarea name="widget[<?php echo $id; ?>][html]" rows="5"><?php echo stripslashes($widget['html']); ?></textarea><br />
						<?php _e('<strong>%link%</strong> = Link to the Deviation; <strong>%id%</strong> = The Deviation Id<br />
						<strong>%title%</strong> = The Deviation Title; <strong>%author%</strong> = The Author of the Deviation<br />
						<strong>%category%</strong> = The Category of the Deviation; <strong>%image%</strong> = The URL of the Image<br />'); ?>
					</div>
				</div>
		</div>
		<?php }} ?>
		</div>
		<div id="Widget-New" class="widgetItem" style="display:none;">

			<div class="controlbox">
				<b>-</b> <a href="#remove" onclick="return removeWidget('Widget-$ID');"><?php _e('Remove Item'); ?></a><br />
				<b>*</b> <a href="#" onclick="return showPHP('$ID');"><?php _e('Show PHP'); ?></a>
			</div>
			<strong><?php _e('Name:'); ?></strong><input type="text" name="widget[$ID][name]" value="dDeviantArt $ID" /><br />
			<input type="radio" name="widget[$ID][type]" value="rss"  onchange="if(this.checked){$('#Widget-$ID-Query').hide();}" />
				<strong><?php _e('RSS Feed:'); ?></strong><input type="text" name="widget[$ID][rss]" value="" /><br />
			<input type="radio" name="widget[$ID][type]" value="query"  onchange="if(this.checked){$('#Widget-$ID-Query').show();}" /> Query<br />
				<div id="Widget-$ID-Query" class="subsection">
					<input type="radio" name="widget[$ID][who]" id="widget-$ID-by" value="by"  /> 
						<strong><?php _e('By:'); ?></strong> <input type="text" name="widget[$ID][byname]" onclick="$('#widget-$ID-by').attr('checked','checked');" value="" />
					<input type="radio" name="widget[$ID][who]" id="widget-$ID-favby" value="favby" />
						<strong><?php _e('FavBy:'); ?></strong> <input type="text" name="widget[$ID][favbyname]" onclick="$('#widget-$ID-favby').attr('checked','checked');" value="" />
					<input type="radio" name="widget[$ID][who]" id="widget-$ID-search"  value="search"  />
						<strong><?php _e('Search:'); ?></strong> <input type="text" name="widget[$ID][search]" onclick="$('#widget-$ID-search').attr('checked','checked');" value="" /><br />
					<strong><?php _e('Order By:'); ?></strong> <select name="widget[$ID][order]">
						<option value="time" selected="selected"><?php _e('Time'); ?></option>
						<option value="popular-24h"><?php _e('Popular: 24 Hours'); ?></option>
						<option value="popular-3d"><?php _e('Popular: 3 Days'); ?></option>
						<option value="popular-1w"><?php _e('Popular: 1 Week'); ?></option>
						<option value="popular-1m"><?php _e('Popular: 1 Month'); ?></option>
						<option value="popular"><?php _e('Popular: All Time'); ?></option>
					</select>
					<?php _e('Random:'); ?> <select name="widget[$ID][random]">
								<option value="0"><?php _e('No'); ?></option>
								<option value="1"><?php _e('Yes'); ?></option>
							</select>
				</div>
				<div id="Widget-$ID-Options" class="">
					<?php _e('Type of image to Display:'); ?> <select name="widget[$ID][deviationtype]">
													<option value="deviations" selected="selected"><?php _e('Deviations'); ?></option>
													<option value="prints" disabled="disabled"><?php _e('Prints'); ?></option>
												</select>
					<?php _e('Include Scraps?:'); ?> <select name="widget[$ID][includescraps]">
											<option value="0" selected="selected"><?php _e('No'); ?></option>
											<option value="1"><?php _e('Yes'); ?></option>
									</select><br />
					<?php _e('Number of images to show:'); ?>
						<select name="widget[$ID][numbertoshow]">
						<?php
							foreach( range(1,24) as $i )
								echo '<option value="' . $i . '"' . selected(24,$i) . '>' . $i . '</option>'; 
						?>
					</select>
					<?php _e('Image Resolution:'); ?>  <select name="widget[$ID][size]">
											<option value="100" disabled="disabled">100px</option>
											<option value="150" selected="selected">150px</option>
											<option value="300W">300px</option>
										</select><br />
					<?php _e('Display Orientation:'); ?>
							<select name="widget[$ID][orientation]">
								<option value="V" selected="selected"><?php _e('Vertical'); ?></option>
								<option value="H" disabled="disabled"><?php _e('Horizontal'); ?></option>
							</select>
					<?php _e('Use inline Styles?'); ?> 
							<select name="widget[$ID][inlinestyles]">
								<option value="1" selected="selected"><?php _e('Yes'); ?></option>
								<option value="0"><?php _e('No'); ?></option>
							</select><br />
					<input type="checkbox" onchange="if(this.checked){ $('#Widget-$ID-Options-Advanced').show(); } else { $('#Widget-$ID-Options-Advanced').hide(); }" /><?php _e('Show Advanced Options'); ?>
					<div id="Widget-$ID-Options-Advanced" style="display:none;">
						<?php _e('HTML:'); ?><br />
						<textarea name="widget[$ID][html]" rows="5"><a target="_blank" href="%link%" title="<?php _e('%title% by %author% in %category%'); ?>">
<img src="%image%" alt="%title% by %author%" />
</a><br />
						</textarea><br />
						<?php _e('<strong>%link%</strong> = Link to the Deviation; <strong>%id%</strong> = The Deviation Id<br />
						<strong>%title%</strong> = The Deviation Title;  <strong>%author%</strong> = The Author of the Deviation<br />
						<strong>%category%</strong> = The Category of the Deviation; <strong>%image%</strong> = The URL of the Image<br />'); ?>
					</div>
				</div>
	</div>
	<p><?php _e('<strong>NOTE:</strong> Changes that are made to <strong>ANY</strong> Widget/Item will be saved, changes are not limited to the currently selected item.'); ?></p>
	<p class="submit">
		<input type="submit" name="submit" value="<?php _e('Save Widget Options &raquo;'); ?>" />
	</p>
	</form>
</div>
<?php
	if( isset($_POST['general_submit']) ){
		update_option('dda_hotlink',$_POST['hotlink']);
		update_option('dda_timeout',$_POST['cacheTimeout']);
		
		update_option('dda_disableinlinethumbnails',$_POST['disableinlinethumbnails']);
		update_option('dda_inlinesize',$_POST['inlinesize']);
		update_option('dda_inlineclass',$_POST['inlineclass']);
		update_option('dda_inlinelink',$_POST['inlinelink']);
		update_option('dda_portfolio_pages',$_POST['portfolio_pages']);
		echo '<div class="updated"><p>'.__('General Options Saved').'</p></div>';
	}
	if( isset($_POST['delete_cache_submit']) ){
		dda_delete_cache();
		dda_flush_rules();
		echo '<div class="updated"><p>'.__('Cache Cleared').'</p></div>';
	}
	if( false == get_option('dda_hotlink') )
		echo '<div class="error"><p>' . __('Please Set the initial <strong>General options</strong>.') . '</p></div>';
?>
<div class="wrap">
	<form name="ddeviantart-generaloptions" method="post" action="options-general.php?page=ddeviantart/dda_options.php">
	<h2><?php _e('General Options'); ?></h2>
	<p>
		<?php _e('Hotlink to images on Deviantart:'); ?> <select name="hotlink">
											<option value="1"<?php selected('1',get_option('dda_hotlink'));?>><?php _e('Yes'); ?></option>
											<option value="0"<?php selected('0',get_option('dda_hotlink'));?> disabled="disabled"><?php _e('No'); ?></option>
										</select><br />
		<?php _e('Check for new images every:'); ?> <select name="cacheTimeout">
											<option value="60"<?php selected('60',get_option('dda_timeout'));?>><?php _e('1 Hour'); ?></option>
											<option value="360"<?php selected('360',get_option('dda_timeout'));?>><?php _e('6 Hours'); ?></option>
											<option value="720"<?php selected('720',get_option('dda_timeout')); /* Lets be nice to DA and set a default of 12 hours: */
																     selected(false,get_option('dda_timeout')); ?>><?php _e('12 Hours'); ?></option>
											<option value="1440"<?php selected('1440',get_option('dda_timeout'));?>><?php _e('1 Day'); ?></option>
											<option value="4320"<?php selected('4320',get_option('dda_timeout'));?>><?php _e('3 Days'); ?></option>
											<option value="10080"<?php selected('10080',get_option('dda_timeout'));?>><?php _e('1 Week'); ?></option>
										</select><br />
		<?php _e('Display Portfolio pages in the Page Listing:'); ?> 
										<select name="portfolio_pages">
											<option value="1"<?php selected('1',get_option('dda_portfolio_pages'));?>><?php _e('Yes'); ?></option>
											<option value="0"<?php selected('0',get_option('dda_portfolio_pages'));?>><?php _e('No'); ?></option>
										</select><br />
	</p>
	<h2><?php _e('Post Thumbnails'); ?></h2>
	<p>
		<?php _e('Thumbnails can be inserted into blog posts using the <strong>Thumb(:<span>thumb51803043</span>:)</strong> string displayed on Deviation pages.'); ?><br />
		<?php _e('These defaults can be overridden by inserting the appropriate modification after the thumb line, eg. <strong>:<span>thumb555:300:imgcenter</span>:</strong> for Deviation 555 in size 300px, with the class "imgcenter" applied to it. No Spaces can be used in the definition.'); ?>
	</p>
	<p>
		<input type="checkbox" name="disableinlinethumbnails" <?php checked(true,get_option('dda_disableinlinethumbnails')); ?> /> <?php _e('Disable Inline Thumbnails'); ?><br />
		<?php _e('Default Size:'); ?> <select name="inlinesize">
						<option value="100" disabled="disabled"<?php selected('100',get_option('dda_inlinesize'));?>>100px</option>
						<option value="150" selected="selected"<?php selected('150',get_option('dda_inlinesize'));?>>150px</option>
						<option value="300W"<?php selected('300W',get_option('dda_inlinesize'));?>>300px</option>
					</select><br/>
		<?php _e('Class to apply to images:'); ?> <input name="inlineclass" value="<?php echo get_option('dda_inlineclass');?>" /><br/>
		<?php _e('Link to Deviations?:'); ?> <select name="inlinelink" >
								<option value="1"<?php selected('1',get_option('dda_inlinelink')); ?>><?php _e('Yes'); ?></option>
								<option value="0"<?php selected('0',get_option('dda_inlinelink')); ?>><?php _e('No'); ?></option>
							</select> <?php _e('(Cannot be overridden)'); ?>
	</p>
	<p class="submit">
		<input type="submit" name="general_submit" value="<?php _e('Save General Options &raquo;'); ?>" /><br />
		<input type="submit" name="delete_cache_submit" value="<?php _e('Clear Cache &raquo;'); ?>" /><br />
	</p>
	</form>
</div>

<?php
if( isset($_POST['portfolio_submit']) ){
	$portfolios = array();
	foreach($_POST['portfolio'] as $key=>$val){
		if( $key === '$ID')
			continue;

		if( empty($val['slug']) )
			$val['slug'] = $val['name'];
		$val['slug'] = sanitize_title_with_dashes($val['slug']);

		//This resets all ID's to 0...n
		$portfolios[] = $val;
	}
	
	//Create the final feed:
	for( $i=0; $i<count($portfolios); $i++){
		if( isset($portfolios[$i]['rss']) && ! empty($portfolios[$i]['rss']) ){
			$portfolios[$i]['feed'] = $portfolios[$i]['rss'];
			continue;
		}
		$portfolios[$i]['feed'] = dda_widget_to_feed($portfolios[$i]);
	}	
	
	update_option('dda_portfolios',$portfolios);

	echo '<div class="updated"><p>'.__('Portfolio Options Saved').'</p></div>';
	//Update the Rewrite rules:
	dda_flush_rules();
}
$portfolios = get_option('dda_portfolios');
?>
<div class="wrap">
	<script type="text/javascript">
		var NextPortfolioId = <?php echo count($portfolios); ?>;
	</script>
	<h2><?php _e('Portfolio Pages'); ?></h2>
	<form name="ddeviantart" method="post" action="options-general.php?page=ddeviantart/dda_options.php">
	<div id="container-portfolio">
		<ul class="tab-nav">
		<?php 
			if( !empty($portfolios) ){
				foreach((array)$portfolios as $id=>$portfolio){
					echo '<li><a href="#Portfolio-'.$id.'" tabindex="1">' . $portfolio['name']. '</a></li>';
				}
			}
		?>
		</ul>
		<a href="#" onclick="return addNewPortfolioPage();" tabindex="1" class="AddNew"><?php _e('[+] New Page'); ?></a>
		<?php 
			if( !empty($portfolios) ){
				foreach((array)$portfolios as $id=>$portfolio){
		?>
		<div id="Portfolio-<?php echo $id; ?>" class="portfolioItem">
			<script type="text/javascript">
				$(document).ready(function() {
					<?php
						if( 'query' != $portfolio['type'] )
							echo "$('#Portfolio-$id-Query').hide();";
					?>
				});
			</script>
			<div class="controlbox">
				<b>-</b> <a href="#remove" onclick="return removePortfolioPage('Portfolio-<?php echo $id; ?>');"><?php _e('Remove Item'); ?></a><br />
			</div>
			<strong><?php _e('Page Name:'); ?></strong><input type="text" name="portfolio[<?php echo $id; ?>][name]" value="<?php echo attribute_escape($portfolio['name']); ?>" /><br />
			<strong><?php _e('Page Slug:'); ?></strong><input type="text" name="portfolio[<?php echo $id; ?>][slug]" value="<?php echo attribute_escape($portfolio['slug']); ?>" /><br />
			<input type="radio" name="portfolio[<?php echo $id; ?>][type]" value="rss" <?php checked('rss',$portfolio['type']); ?> onchange="if(this.checked){$('#Portfolio-<?php echo $id; ?>-Query').hide();}" />
				<strong><?php _e('RSS Feed:'); ?></strong><input type="text" name="portfolio[<?php echo $id; ?>][rss]" value="<?php echo attribute_escape($portfolio['rss']); ?>" /><br />
			<input type="radio" name="portfolio[<?php echo $id; ?>][type]" value="query" <?php checked('query',$portfolio['type']); ?> onchange="if(this.checked){$('#Portfolio-<?php echo $id; ?>-Query').show();}" /> <?php _e('Query'); ?><br />
				<div id="Portfolio-<?php echo $id; ?>-Query" class="subsection">
					<input type="radio" name="portfolio[<?php echo $id; ?>][who]" id="portfolio-<?php echo $id; ?>-by" value="by" <?php checked('by',$portfolio['who']); ?> /> 
						<strong><?php _e('By:'); ?></strong> <input type="text" name="portfolio[<?php echo $id; ?>][byname]" onclick="$('#portfolio-<?php echo $id; ?>-by').attr('checked','checked');" value="<?php echo attribute_escape($portfolio['byname']); ?>" />
					<input type="radio" name="portfolio[<?php echo $id; ?>][who]" id="portfolio-<?php echo $id; ?>-favby" value="favby" <?php checked('favby',$portfolio['who']); ?> />
						<strong><?php _e('FavBy:'); ?></strong> <input type="text" name="portfolio[<?php echo $id; ?>][favbyname]" onclick="$('#portfolio-<?php echo $id; ?>-favby').attr('checked','checked');" value="<?php echo attribute_escape($portfolio['favbyname']); ?>" />
					<input type="radio" name="portfolio[<?php echo $id; ?>][who]" id="portfolio-<?php echo $id; ?>-search"  value="search" <?php checked('search',$portfolio['who']); ?> />
						<strong><?php _e('Search:'); ?></strong> <input type="text" name="portfolio[<?php echo $id; ?>][search]" onclick="$('#portfolio-<?php echo $id; ?>-search').attr('checked','checked');" value="<?php echo attribute_escape($portfolio['search']); ?>" /><br />
					<strong><?php _e('Order By:'); ?></strong> <select name="portfolio[<?php echo $id; ?>][order]">
						<option value="time"<?php selected('time',$portfolio['order']); ?>><?php _e('Time'); ?></option>
						<option value="popular-24h"<?php selected('popular-24h',$portfolio['order']); ?>><?php _e('Popular: 24 Hours'); ?></option>
						<option value="popular-3d"<?php selected('popular-3d',$portfolio['order']); ?>><?php _e('Popular: 3 Days'); ?></option>
						<option value="popular-1w"<?php selected('popular-1w',$portfolio['order']); ?>><?php _e('Popular: 1 Week'); ?></option>
						<option value="popular-1m"<?php selected('popular-1m',$portfolio['order']); ?>><?php _e('Popular: 1 Month'); ?></option>
						<option value="popular"<?php selected('popular',$portfolio['order']); ?>><?php _e('Popular: All Time'); ?></option>
					</select>
					<?php _e('Random:'); ?> <select name="portfolio[<?php echo $id; ?>][random]">
								<option value="0"<?php selected('0',$portfolio['random']);?>><?php _e('No'); ?></option>
								<option value="1"<?php selected('1',$portfolio['random']);?>><?php _e('Yes'); ?></option>
							</select>
				</div>
				<div id="Portfolio-<?php echo $id; ?>-Options" class="">
					<?php _e('Type of image to Display:'); ?> <select name="portfolio[<?php echo $id; ?>][deviationtype]">
													<option value="deviations"<?php selected('deviations',$portfolio['deviationtype']);?>><?php _e('Deviations'); ?></option>
													<option value="prints"<?php selected('prints',$portfolio['deviationtype']);?> disabled="disabled"><?php _e('Prints'); ?></option>
												</select>
					<?php _e('Include Scraps?:'); ?> <select name="portfolio[<?php echo $id; ?>][includescraps]">
											<option value="0"<?php selected('0',$portfolio['includescraps']);?>><?php _e('No'); ?></option>
											<option value="1"<?php selected('1',$portfolio['includescraps']);?>><?php _e('Yes'); ?></option>
									</select><br />
					<?php _e('Number of images to show:'); ?>
						<select name="portfolio[<?php echo $id; ?>][numbertoshow]">
						<?php foreach( range(1,24) as $i)
								echo '<option value="' . $i . '"' . selected($portfolio['numbertoshow'], $i) . '>' . $i . '</option>'; 
							  ?>
						</select>
					<?php _e('Image Resolution:'); ?>  <select name="portfolio[<?php echo $id; ?>][size]">
											<option value="100"<?php selected('100',$portfolio['size']);?> disabled="disabled">100px</option>
											<option value="150"<?php selected('150',$portfolio['size']);?>>150px</option>
											<option value="300W"<?php selected('300W',$portfolio['size']);?>>300px</option>
										</select><br />
					<?php _e('Use inline Styles?'); ?> 
							<select name="portfolio[<?php echo $id; ?>][inlinestyles]">
								<option value="1"<?php selected('1',$portfolio['inlinestyles']);?>><?php _e('Yes'); ?></option>
								<option value="0"<?php selected('0',$portfolio['inlinestyles']);?>><?php _e('No'); ?></option>
							</select> 
					<?php _e('Display sidebar:'); ?>
							<select name="portfolio[<?php echo $id; ?>][sidebar]">
								<option value="top"<?php selected('top',$portfolio['sidebar']); ?>><?php _e('Before Content'); ?></option>
								<option value="bottom"<?php selected('bottom',$portfolio['sidebar']); ?>><?php _e('After Conten'); ?>t</option>
								<option value="none"<?php selected('none',$portfolio['sidebar']); ?>><?php _e('Do not display Sidebar'); ?></option>
							</select><br />
				</div>
		</div>
		<?php }} ?>
		</div>
		<div id="Portfolio-New" class="portfolioItem" style="display:none;">

			<div class="controlbox">
				<b>-</b> <a href="#remove" onclick="return removePortfolioPage('Portfolio-$ID');"><?php _e('Remove Item'); ?></a><br />
			</div>
			<strong><?php _e('Page Name:'); ?></strong><input type="text" name="portfolio[$ID][name]" value="Portfolio $ID" /><br />
			<strong><?php _e('Page Slug:'); ?></strong><input type="text" name="portfolio[$ID][slug]" value="portfolio-$ID" /><br />
			<input type="radio" name="portfolio[$ID][type]" value="rss"  onchange="if(this.checked){$('#Portfolio-$ID-Query').hide();}" />
				<strong><?php _e('RSS Feed:'); ?></strong><input type="text" name="portfolio[$ID][rss]" value="" /><br />
			<input type="radio" name="portfolio[$ID][type]" value="query"  onchange="if(this.checked){$('#Portfolio-$ID-Query').show();}" /> Query'); ?><br />
				<div id="Portfolio-$ID-Query" class="subsection">
					<input type="radio" name="portfolio[$ID][who]" id="portfolio-$ID-by" value="by"  /> 
						<strong><?php _e('By:'); ?></strong> <input type="text" name="portfolio[$ID][byname]" onclick="$('#portfolio-$ID-by').attr('checked','checked');" value="" />
					<input type="radio" name="portfolio[$ID][who]" id="portfolio-$ID-favby" value="favby" />
						<strong><?php _e('FavBy:'); ?></strong> <input type="text" name="portfolio[$ID][favbyname]" onclick="$('#portfolio-$ID-favby').attr('checked','checked');" value="" />
					<input type="radio" name="portfolio[$ID][who]" id="portfolio-$ID-search"  value="search"  />
						<strong><?php _e('Search:'); ?></strong> <input type="text" name="portfolio[$ID][search]" onclick="$('#portfolio-$ID-search').attr('checked','checked');" value="" /><br />
					<strong><?php _e('Order By:'); ?></strong> <select name="portfolio[$ID][order]">
						<option value="time" selected="selected"><?php _e('Time'); ?></option>
						<option value="popular-24h"><?php _e('Popular: 24 Hours'); ?></option>
						<option value="popular-3d"><?php _e('Popular: 3 Days'); ?></option>
						<option value="popular-1w"><?php _e('Popular: 1 Week'); ?></option>
						<option value="popular-1m"><?php _e('Popular: 1 Month'); ?></option>
						<option value="popular"><?php _e('Popular: All Time'); ?></option>
					</select>
					<?php _e('Random:'); ?> <select name="portfolio[$ID][random]">
								<option value="0"><?php _e('No'); ?></option>
								<option value="1"><?php _e('Yes'); ?></option>
							</select>
				</div>
				<div id="Portfolio-$ID-Options" class="">
					<?php _e('Type of image to Display:'); ?> <select name="portfolio[$ID][deviationtype]">
													<option value="deviations" selected="selected"><?php _e('Deviations'); ?></option>
													<option value="prints" disabled="disabled"><?php _e('Prints'); ?></option>
												</select>
					<?php _e('Include Scraps?:'); ?> <select name="portfolio[$ID][includescraps]">
											<option value="0" selected="selected"><?php _e('No'); ?></option>
											<option value="1"><?php _e('Yes'); ?></option>
									</select><br />
					<?php _e('Number of images to show:'); ?>
						<select name="portfolio[$ID][numbertoshow]">
						<?php foreach( range(1,24) as $i)
								echo '<option value="' . $i . '"' . selected(24, $i) . '>' . $i . '</option>'; 
						?>
						</select>
					<?php _e('Image Resolution:'); ?>  <select name="portfolio[$ID][size]">
											<option value="100" disabled="disabled">100px</option>
											<option value="150">150px</option>
											<option value="300W" selected="selected">300px</option>
										</select><br />
					<?php _e('Use inline Styles?'); ?> 
							<select name="portfolio[$ID][inlinestyles]">
								<option value="1" selected="selected"><?php _e('Yes'); ?></option>
								<option value="0"><?php _e('No'); ?></option>
							</select>
					<?php _e('Display sidebar:'); ?>
							<select name="portfolio[$ID][sidebar]">
								<option value="top"><?php _e('Before Content'); ?></option>
								<option value="bottom" selected="selected"><?php _e('After Content'); ?></option>
								<option value="none"><?php _e('Do not display Sidebar'); ?></option>
							</select><br />
				</div>
	</div>
	<p class="submit">
		<input type="submit" name="portfolio_submit" value="<?php _e('Save Portfolio Options &raquo;'); ?>" />
	</p>
	</form>
	<?php if( ! empty($portfolios) ){ ?>
	<h2><?php _e('Portfolio URIs'); ?></h2>
	<table class="widefat plugins">
		<thead>
		<tr>
			<th><?php _e('Name'); ?></th>
			<th><?php _e('URL'); ?></th>
			<th><?php _e('URL(with Pretty permalinks)'); ?></th>
		</tr>
		</thead>
	<?php foreach((array)$portfolios as $id=>$item){ 
			$style = ('class="alternate"' == $style) ? '' : 'class="alternate"';
		?>
		<tr <?php echo $style; ?>>
			<td><?php echo $item['name']; ?></td>
			<td><?php 
					$url = get_bloginfo('siteurl');
					if( substr($url,-1) != '/' )
						$url .= '/';
					$url .= '?ddeviantart=' . $item['slug']; 
					echo "<a href='$url'>$url</a>";
					?></td>
			<td><?php 
					$url = get_bloginfo('siteurl') . '/' . $item['slug'] . '/'; 
					echo "<a href='$url'>$url</a>";
				?></td>
		</tr>
	<?php } ?>
	</table>
	<?php } ?>
</div>
