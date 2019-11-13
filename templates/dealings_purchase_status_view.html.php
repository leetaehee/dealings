<form id="dealinges-purchase-status-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p><h3>[<?=TITLE_VOUCHER_PURCHASE_STATUS?>]</h3></p>
	<p>---------------------------------------</p>
	<p>1. 제목: <?=$dealingsSubject?></p>
	<p>2. 내용: <?=$dealingsContent?></p>
	<p>3. 구매등록일 : <?=$registerDate?></p>
	<p>4. 구매종료일 : <?=$expirationDate?></p>
	<p>5. 구매자 정보: <?=setDecrypt($name)?>(<?=$id?>)</p>
    <p>6. 상품권분류: <?=$itemName?></p>
	<p>7. 상품권가격: <?=$itemMoney?>원</p>
	<p>8. 거래상태: <?=$dealingsStatusName?></p>
	<p>9. 상품권 핀번호: 결제 및 관리자가 확인 후 문자로 보내드립니다.</p>
	<?php if(!empty($memo)): ?>
		<p>10. 비고: <?=$memo?></p>
	<?php endif;?>
	<p>11. 상품권 거래금액: <?=$dealingsMileage?>원</p>
	<p>12. 수수료: <?=number_format($commission)?>원 (<?=$dealingsCommission?>%)</p>
    <p>13. 판매 후 실수령액:
        <span id="realPaymentSum"><?=number_format($finalPaymentSum);?></span>원
	</p>
	<?php if (!empty($couponIdx)): ?>
		14. 쿠폰 적용 했을 때 실수령액: <?=number_format($finalRealPaymentSum)?>원
	<?php endif; ?>
	<?php if (!empty($couponIdx)): ?>
		<p>---------------------------------------</p>
		<p>[쿠폰 사용 내용]</p>
		<p>. 사용한 쿠폰 정보: "<?=$couponSubject?>"
		</p>
		<p>
			2. 쿠폰할인율 : 
			<?php if($temMoney == 0): ?>
				모든금액 적용쿠폰
			<?php else: ?>
				<?=$itemMoney?>원
			<?php endif; ?>
			(<?=$discountRate?>% 쿠폰)
		</p>
		<p>3. 할인금액:  <?=number_format($discountMoney)?>원</p>
	<?php endif; ?>
	
	<?php if($dealingsStatus == 2): ?>
		<p>
			<input type="button" id="submit-btn" value="<?=$btnName?>">
		</p>
	<?php endif; ?>

	<?php if($dealingsStatus == 3): ?>
		<span>
			<a href="<?=$dealingsApproval?>">[거래승인]</a>
		</span>
		<span class="pl">
			<a href="<?=$dealingsCancel?>">[거래취소]</a>
		</span>
	<?php endif; ?>
</form>