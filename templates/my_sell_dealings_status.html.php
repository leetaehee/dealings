<form id="my-sell-dealings-status" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p><h3>[<?=TITLE_VOUCHER_SELL_ENROLL_STATUS?>]</h3></p>
	<p>---------------------------------------</p>
	<p>1. 제목: <?=$dealingsSubject?></p>
	<p>2. 내용: <?=$dealingsContent?></p>
	<p>3. 판매등록일 : <?=$registerDate?></p>
	<p>4. 판매종료일 : <?=$expirationDate?></p>
	<p>5. 판매자 정보: <?=setDecrypt($sellerName)?>(<?=$sellerId?>)</p>
	<p>6. 상품권분류: <?=$itemName?></p>
	<p>7. 상품권가격: <?=$itemMoney?>원</p>
	<p>8. 거래상태: <?=$dealingsStatusName?></p>
	<?php if(!empty($itemObjectNo)): ?>
		<?php if($dealingsStatus==4): ?>
			<p>9. 상품권 핀번호: <?=setDecrypt($itemObjectNo)?></p>
		<?php else: ?>
			<p>9. 상품권 핀번호: 핀번호는 결제를 진행한 후 관리자가 승인해야 노출됩니다.</p>
		<?php endif; ?>
	<?php endif; ?>
	<?php if(!empty($dealingdsMemo)): ?>
		<p>10. 비고: <?=$dealingsMemo?></p>
	<?php endif;?>
	<p>11. 상품권 거래금액: <?=$dealingsMileage?>원</p>
	<p>12. 수수료: <?=number_format($commission)?>원 (<?=$dealingsCommission?>%)</p>
    <p>13. 판매 후 실수령액: <span id="realPaymentSum"><?=number_format($finalPaymentSum);?></span>원</p>

	<?php if (!empty($useCouponIdx)): ?>
		14. 쿠폰 적용 했을 때 실수령액: <?=number_format($finalRealPaymentSum)?>원
	<?php endif; ?>

	<?php if (!empty($useCouponIdx)): ?>
		<p>---------------------------------------</p>
		<p>[쿠폰 사용 내용]</p>
		<p>1. 사용한 쿠폰 정보: "<?=$useCpSubject?>" <br></p>
		<p>
			2. 쿠폰할인율 :
            <?php if($useCpItemMoney== 0): ?>
                모든금액 적용쿠폰
            <?php else: ?>
                <?=$useCpDiscountRate?>원
            <?php endif; ?>
            (<?=$useCpDiscountRate?>% 쿠폰)
		</p>
		<p>3. 할인금액:  <?=number_format($useCpDiscountMoney)?>원</p>
	<?php endif; ?>

	<?php if ($dealingsStatus == 2): ?>
		<p><input type="button" id="submit-btn" value="<?=$btnName?>"></p>
	<?php endif ?>

	<?php if ($dealingsStatus > 1): ?>
		<p>---------------------------------------</p>
		<p>[구매자정보]</p>
		<p>
			1. 이름: <?=setDecrypt($purchaserName)?>
			(<?=$purchaserId?>)
			<input type="hidden" value="<?=$purchaserId?>">
		</p>
		<p>2. 연락처 <?=setDecrypt($purchaserEmail)?></p>
		<p>3. 이메일: <?=setDecrypt($purchaserPhone)?></p>
		<p>---------------------------------------</p>
	<?php endif;?>

	<?php if($dealingsStatus == 3): ?>
		<span>
			<a href="<?=$dealingsApproval?>">[거래승인]</a>
		</span>
		<span class="pl">
			<a href="<?=$dealingsCancel?>">[거래취소]</a>
		</span>
	<?php endif; ?>

	<?php if($dealingsStatus < 2): ?>
		<span>
			<a href="<?=$dealingsModifyUrl?>">[수정]</a>
		</span>
		<span class="pl">
			<a href="<?=$dealingsDeleteUrl?>">[삭제]</a>
		</span>
	<?php endif; ?>
</form>