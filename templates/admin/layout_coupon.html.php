<!doctype html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title><?=$title?></title>

		<link rel="stylesheet" href="<?=NOMALIZE_CSS_URL;?>">
		<style>
			body { text-align: center; width: 1300px; }
			div#wrapper { width: 100%; text-align: left; min-height: 300px; margin: 0 auto;}
			header, footer, nav, aside, section { border: 1px solid #999; margin: 5px; padding: 10px; }
			nav { height: 30px;}
			section, aside { height: 720px; float: left; }
			aside { width: 182px; } 
			section { width: 1052px; overflow: scroll; }
			header { height: 50px; }
			footer { clear: both; } 
			.ul-nav { list-style: none; margin: 0; padding: 0; }
			.ul-nav li { margin: 0 0 0 0; padding: 0 0 0 0; border: 0; float: left; }
            .pl-3 { padding-left: 3px; }
			.pt-15 { padding-top: 15px; }
			.table { font-size: 10pt; border: 1px solid #444444; border-collapse: collapse; }
			.table th, .table td { border: 1px solid #444444; padding: 2px; }
			.empty-tr-colspan { text-align: center; color: red; }
			.dealings-table-width { width: 1030px; }
		</style>
    </head>
    <body>
		<div id="wrapper">
			<header>
				<h1>상품권 거래 사이트 (관리자)</h1>
			</header>
			<nav>
				<ul class="ul-nav">
					<li>
						<a href="<?=SITE_ADMIN_DOMAIN?>/admin.php">Home|</a>
					</li>
					<?php if(isset($_SESSION['mIdx'])): ?>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/logout.php">로그아웃|</a>
						</li>
						<li> 
							<a href="<?=SITE_ADMIN_DOMAIN?>/coupon.php">쿠폰관리|</a>
						</li>
						<li> 
							<a href="<?=SITE_ADMIN_DOMAIN?>/admin_event.php">이벤트|</a>
						</li>
						<li> 
							<a href="<?=SITE_ADMIN_DOMAIN?>/admin_page.php">관리자</a>
						</li>
					<?php else: ?>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/login.php">로그인|</a>
						</li>
						<li>
							<a href="<?=SITE_ADMIN_DOMAIN?>/join.php">회원가입</a>
						</li>
					<?php endif; ?>
				</ul>
			</nav>
			<aside>
				<p>[쿠폰관리]</p>
				<ul>
					<li>
						<a href="<?=SITE_ADMIN_DOMAIN?>/issue_coupon.php">쿠폰발행</a>
					</li>
					<li>
						<a href="<?=SITE_ADMIN_DOMAIN?>/coupon_member_status.php">쿠폰지급</a>
					</li>
					<li>
						<a href="<?=SITE_ADMIN_DOMAIN?>/coupon_issue_list.php">발행내역</a>
					</li>
					<li>
						<a href="<?=SITE_ADMIN_DOMAIN?>/coupon_use_list.php">사용내역</a>
					</li>
				</ul>
			</aside>
			<section>
				<?php include_once $templateFileName?>
			</section>
			<footer>
				&copy; 상품권 거래 시스템 <?=date('Y')?> <br>
				사이트에 오류가 있을 경우 관리자에게 문의바랍니다.
			</footer>
		</div>
		<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
		<script>
			var ajaxUrl = "<?=$ajaxUrl?>";
		</script>
		<script src="<?=COMMON_JS_URL;?>"></script>
		<?php if(!empty($JsTemplateUrl)): ?>
			<script src="<?=$JsTemplateUrl;?>"></script>
		<?php endif; ?>
    </body>
</html>