<p>
	<h3>[<?=TITLE_COUPON_USEAGE?>]</h3>
</p>
<p>
	- 모든상품권/금액 쿠폰은 결제금액이 0원으로 표시 됩니다.
</p>
<p>
	- 실제 거래되는 물건 거래금액에 할인율을 계산하면됩니다.
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 5%;">
		<col style="width: 7%;">
		<col style="width: 8%;">
		<col style="width: 21%;">
		<col style="width: 21%;">
		<col style="width: 10%;">
		<col style="width: 7%;">
		<col style="width: 10%;">
		<col style="width: 11%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>구분</th>
			<th>회원이름</th>
			<th>사용쿠폰명칭</th>
			<th>상품권종류</th>
			<th>결제금액</th>
			<th>할인율</th>
			<th>할인금액</th>
			<th>환불일자</th>
		</tr>
	</thead>
	<tbody>
		<?php if($couponUseListCount > 0): ?>
			<?php foreach($couponUseList as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=$value['issue_type']?></td>
					<td><?=setDecrypt($value['name'])?></td>
					<td><?=$value['subject']?></td>
					<td><?=$value['item_name']?></td>
					<td><?=number_format($value['coupon_use_before_mileage'])?><!-- 수수료는 표기할것 (수수료)--></td>
					<td><?=number_format($value['discount_rate'])?>%</td>
					<td><?=number_format($value['coupon_use_mileage'])?></td>
					<td>
						<?php if($value['is_refund']=='Y'): ?>
							<?=$value['coupon_use_end_date']?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="9" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>