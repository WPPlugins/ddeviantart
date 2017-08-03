<?php 
$curPage = strtolower($wp_query->query_vars['ddeviantart']);//The curent Port folio page

$portfolioPages = get_option('dda_portfolios');
$portfolio = false;
foreach( (array) $portfolioPages as $pPage){
	if( $pPage['slug'] == $curPage ){
		$portfolio = $pPage; //If current page is this Page, then set
		break;
	}
}
if( ! $portfolio ){
	//We couldnt find that photo page.. Redirect to main page.
	wp_redirect( get_bloginfo('siteurl') );
}

if( 'no' != $portfolio['inlinestyles'])
	add_action('wp_head','dda_portfolio_head');
?>
<?php get_header(); ?>
<?php function dda_portfolio_head(){ ?>
<style type="text/css">
	.deviation{
		display:inline;
		margin-right:20px;
		vertical-align:top;
		text-align:center;
		color:#999999;
	}
	.deviation span{
		display:inline-block;
		border:thin solid #CCCCCC;
		margin-bottom:40px;
	}
	.deviation strong{
		color:#000000;
	}
</style>
<?php } ?>
<?php if( 'top' == $portfolio['sidebar'] ) get_sidebar(); ?>
<?php 
	if( !empty($portfolio['name']) )
		echo '<h1>' . $portfolio['name'] . '</h1>';
	$items = dda_get_feed_items($portfolio);

	foreach( (array) $items as $item){
		echo '<div class="deviation"><span>
				<strong>'.$item['title'].'</strong><br/>
				<a href="'.$item['link'].'" target="_blank" title="'.attribute_escape($item['title']).'">
					<img src="'.$item['imagelink'].'" alt="'. attribute_escape($item['title'].' in ' . $item['category']) . 
					'" title="'.attribute_escape($item['title'].' in ' . $item['category'] . ' ' . $item['pubdate']). '" />
				</a><br />
				in '.$item['category'].'<br/>
			</span></div> &nbsp; ';
	}
?>
<?php if( 'bottom' == $portfolio['sidebar'] ) get_sidebar(); ?>
<?php get_footer(); ?>