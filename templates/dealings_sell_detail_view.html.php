<form id="dealinges-sell-detail-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p><h3>[<?=TITLE_VOUCHER_SELL_DETAIL_VIEW?>]</h3></p>
	<p>---------------------------------------</p>
	<p>1. 제목: <?=$dealingsSubject?></p>
	<p>2. 내용: <?=$dealingsContent?></p>
	<p>3. 판매등록일 : <?=$registerDate?></p>
	<p>4. 판매종료일 : <?=$expirationDate?></p>
	<p>5. 판매자 정보: <?=setDecrypt($name)?>(<?=$id?>)</p>
	<p>6. 상품권분류: <?=$itemName?></p>
	<p>7. 상품권가격: <?=$itemMoney?>원</p>
	<p>8. 상품권 거래금액: <?=$dealingsMileage?>원(수수료: <?=$dealingsCommission?>%)</p>
	<?php if(!empty($memo)): ?>
		<p>9. 비고: <?=$memo?></p>
	<?php endif;?>
	<p>10. 거래상태: <?=$dealingsStatusName?></p>
	<p>11. 상품권 핀번호: 결제 및 관리자가 확인 후 문자로 보내드립니다.</p>
	<?php if($writerIdx != $_SESSION['idx']): ?>
		<p><input type="button" id="submit-btn" value="<?=$btnName?>"></p>
	<?php endif; ?>
</form>