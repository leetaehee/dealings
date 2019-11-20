<p>
	<h3>[<?=TITLE_MY_COUPON_LIST?>]</h3>
</p>
<p> - 쿠폰종료일을 참고하시길 바랍니다. </p>
<p> - 쿠폰종료일이 지나면 나의쿠폰조회에서 조회되지않습니다.</p>
<p> - 결제금액이 0원이 경우 금액에 상관없이 적용되며 거래금액에 할인율로 적용됩니다. </p>
<p> - 모든상품권은 상품권 전체에 사용할 수 있습니다.</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 6%;">
		<col style="width: 28%;">
		<col style="width: 14%;">
		<col style="width: 9%;">
		<col style="width: 7%;">
		<col style="width: 10%;">
		<col style="width: 10%;">
		<col style="width: 10%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>구분</th>
			<th>발행쿠폰명칭</th>
			<th>상품권종류</th>
			<th>상품권금액</th>
			<th>할인율</th>
			<th>쿠폰시작일</th>
			<th>쿠폰종료일</th>
			<th>쿠폰사용여부</th>
		</tr>
	</thead>
	<tbody>
		<?php if($myCouponDataCount > 0): ?>
			<?php foreach($myCouponData as $key => $value): ?>
				<tr>
					<td><?=$value['seq']?></td>
					<td><?=$value['issue_type']?></td>
					<td><?=$value['subject']?></td>
					<td><?=$value['item_name']?></td>
					<td><?=$value['item_money']?>원</td>
					<td><?=$value['discount_rate']?>%</td>
					<td><?=$value['start_date']?></td>
					<td><?=$value['expiration_date']?></td>
					<td>
						<?php if($value['coupon_status'] == 2): ?>
							사용함
						<?php else: ?>
							미사용
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