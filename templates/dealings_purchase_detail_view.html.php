<form id="dealinges-purchase-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p><h3>[<?=TITLE_VOUCHER_PURCHASE_DETAIL_VIEW?>]</h3></p>
	<p>---------------------------------------</p>
	<p>1. 제목: <?=$dealingsSubject?></p>
	<p>2. 내용: <?=$dealingsContent?></p>
	<p>3. 구매등록일 : <?=$registerDate?></p>
	<p>4. 구매종료일 : <?=$expirationDate?></p>
	<p>5. 구매자 정보: <?=setDecrypt($name)?><?=$id?>)</p>
	<p>6. 상품권분류: <?=$itemName?></p>
	<p>7. 상품권가격: <?=$itemMoney?>원</p>
	<p>8. 상품권 거래금액: <?=$dealingsMileage?>원(수수료: <?=$dealingsCommission?>%)
	</p>
	<?php if(!empty($memo)): ?>
		<p>9. 비고: <?=$memo?></p>
	<?php endif;?>
	<p>10. 거래상태: <?=$dealingsStatusName?></p>
	<p>11. 상품권 핀번호: 결제 및 관리자가 확인 후 문자로 보내드립니다.</p>
	<?php if($writerIdx != $_SESSION['idx']): ?>
		<?php if($couponUseCount > 0): ?>
			<p>---------------------------------------</p>
			<p>[사용 가능한 쿠폰정보]
			</p>
			<p>
				1. 쿠폰선택: 
				<select id="coupon-name" name="coupon_name">
					<option value="">선택하세요.</option>
					<?php foreach($rCouponUseWaitResult as $key => $value): ?>
						<option value="<?=$value['idx']?>">
							<?=$value['subject']?>
                            (<?=$value['discount_rate']?>%)
						</option>
					<?php endforeach;?>
				</select>
				<br>(쿠폰을 사용하실 경우 판매 수수료에 할인을 받을 수 있습니다.)
			</p>
		<?php else: ?>
			<p>---------------------------------------</p>
			<p>[사용 가능한 쿠폰정보]</p>
			<p>1. 쿠폰선택: 적용 할 쿠폰이 없습니다.</p>
		<?php endif; ?>

		<p><input type="button" id="submit-btn" value="<?=$btnName?>"></p>
	<?php endif; ?>
</form>