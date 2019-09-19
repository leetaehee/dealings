<!doctype html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title><?=$title?></title>

		<link rel="stylesheet" href="<?=NOMALIZE_CSS_URL;?>">
		<style>
			body { text-align: center; width: 900px; }
			div#wrapper { width: 100%; text-align: left; min-height: 300px; margin: 0 auto;}
			header, footer, nav, aside, section { border: 1px solid #999; margin: 5px; padding: 10px; }
			nav { height: 30px;}
			section, aside { height: 500px; float: left; }
			aside { width: 150px; } 
			section { width: 685px; }
			header { height: 50px; }
			footer { clear: both; } 
			.ul-nav { list-style: none; margin: 0; padding: 0; }
			.ul-nav li { margin: 0 0 0 0; padding: 0 0 0 0; border: 0; float: left; }
            .pl-3 { padding-left: 3px; }
			.pt-15 { padding-top: 15px; }
			.table { font-size: 4pt; border: 1px solid #444444; border-collapse: collapse; }
			.admin-table-width { width: 1090px; }
			.mileage-table-width { width: 620px; }
			.login-table-width { width: 630px; }
			.charge-table-width { width: 650px; }
			.table th, .table td { border: 1px solid #444444; padding: 2px; }
			.empty-tr-colspan { text-align: center; color: red; }
		</style>
    </head>
    <body>
		<div id="wrapper">
			<header>
				<h1>IMI</h1>
			</header>
			<nav>
				<ul class="ul-nav">
					<li>
						<a href="<?=SITE_DOMAIN?>/imi.php">Home|</a>
					</li>
					<?php if(isset($_SESSION['idx'])): ?>
						<li>
							<a href="<?=SITE_DOMAIN?>/logout.php">로그아웃|</a>
						</li>
						<li> 
							<a href="<?=SITE_DOMAIN?>/voucher_dealings.php">상품권거래|</a>
						</li>
						<li> 
							<a href="<?=SITE_DOMAIN?>/mypage.php">마이룸</a>
						</li>
					<?php else: ?>
						<li>
							<a href="<?=SITE_DOMAIN?>/login.php">로그인|</a>
						</li>
						<li>
							<a href="<?=SITE_DOMAIN?>/join.php">회원가입</a>
						</li>
					<?php endif; ?>
				</ul>
			</nav>
			<aside>
				<p class="pt-15">[개인정보]</p>
				<ul>
					<li>
						<a href="<?=SITE_DOMAIN?>/mypage.php">내정보 조회</a>
					</li>
					<li>
						<a href="<?=SITE_DOMAIN?>/login_access_list.php">로그인 내역</a>
					</li>
				</ul>
				<p class="pt-15">[마일리지]</p>
				<ul>
					<li>
						<a href="<?=SITE_DOMAIN?>/my_charge_list.php">마일리지 충전내역</a>
					</li>
					<li>
						<a href="<?=SITE_DOMAIN?>/my_withdrawal_list.php">마일리지 출금내역</a>
					</li>
				</ul>
				<p class="pt-15">[마일리지 충전]</p>
				<ul>
					<li>
						<a href="<?=SITE_DOMAIN?>/virtual_account_charge.php">가상계좌 충전</a>
					</li>
					<li>
						<a href="<?=SITE_DOMAIN?>/card_charge.php">신용카드 충전</a>
					</li>
					<li>
						<a href="<?=SITE_DOMAIN?>/phone_charge.php">휴대전화 충전</a>
					</li>
					<li>
						<a href="<?=SITE_DOMAIN?>/voucher_charge.php">문화상품권 충전</a>
					</li>
				</ul>

				<p class="pt-15">[마일리지 출금]</p>
				<ul>
					<li>
						<a href="<?=SITE_DOMAIN?>/virtual_account_mileage_withdrawal.php">가상계좌 출금</a>
					</li>
				</ul>
			</aside>
			<section>
				<?php include_once $templateFileName?>
			</section>
			<footer>
				&copy; IMI 회원정보 시스템 <?=date('Y')?> <br>
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