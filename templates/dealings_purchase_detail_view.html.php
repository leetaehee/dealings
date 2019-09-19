<form id="dealinges-purchase-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
	<input type="hidden" name="dealings_idx" value="<?=$getData['idx']?>">
	<input type="hidden" name="dealings_writer_idx" value="<?=$dealingsData->fields['writer_idx']?>">
	<input type="hidden" name="dealings_status" value="<?=$getData['type']?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p>
		<h3>[<?=TITLE_VOUCHER_PURCHASE_DETAIL_VIEW?>]</h3>
	</p>

	<p>---------------------------------------</p>

	<p>
		1. 제목: <?=$dealingsData->fields['dealings_subject']?>
	</p>

	<p>
		2. 내용: <?=$dealingsData->fields['dealings_content']?>
	</p>

	<p>
		3. 구매등록일 : <?=$dealingsData->fields['register_date']?>
	</p>

	<p>
		4. 구매종료일 : <?=$dealingsData->fields['expiration_date']?>
	</p>

	<p>
		5. 구매자 정보: 
		<?=setDecrypt($dealingsData->fields['name'])?>
		(<?=$dealingsData->fields['id']?>)
	</p>

	<p>
		6. 상품권분류: <?=$dealingsData->fields['item_name']?>
	</p>

	<p>
		7. 상품권가격: <?=$dealingsData->fields['item_money']?>원
	</p>

	<p>
		8. 상품권 거래금액: <?=$dealingsData->fields['dealings_mileage']?>원 
		(수수료: <?=$dealingsData->fields['dealings_commission']?>%)
	</p>

	<p>
		9. 거래상태: <?=$dealingsData->fields['dealings_status_name']?>
	</p>

	<p>
		10. 상품권 핀번호: 결제 및 관리자가 확인 후 문자로 보내드립니다.
	</p>
	
	<?php if($dealingsData->fields['writer_idx'] != $_SESSION['idx']): ?>
		<p>
			<input type="button" id="submit-btn" value="<?=$btnName?>">
		</p>
	<?php endif; ?>
</form>