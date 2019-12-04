<p><h3>[<?=TITLE_COUPON_ISSUE?>]</h3></p>
<p>- 모든상품권/금액 쿠폰은 결제금액이 0원으로 표시 됩니다.</p>
<p>- 실제 거래되는 물건 거래금액에 할인율을 계산하면됩니다.</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 5%;">
		<col style="width: 7%;">
		<col style="width: 21%;">
		<col style="width: 21%;">
		<col style="width: 10%;">
		<col style="width: 7%;">
		<col style="width: 9%;">
		<col style="width: 10%;">
		<col style="width: 10%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>구분</th>
			<th>사용쿠폰명칭</th>
			<th>상품권종류</th>
			<th>결제금액</th>
			<th>할인율</th>
			<th>할인금액</th>
			<th>시작일자</th>
			<th>종료일자</th>
		</tr>
	</thead>
	<tbody>
		<?php if($couponDataCount > 0): ?>
			<?php foreach($couponData as $key => $value): ?>
				<tr>
					<td><?=$value['seq']?></td>
					<td><?=$value['issue_type']?></td>
					<td><?=$value['subject']?></td>
					<td><?=$value['item_name']?></td>
					<td><?=number_format($value['item_money'])?></td>
					<td><?=$value['discount_rate']?>%</td>
					<td><?=number_format($value['discount_mileage'])?></td>
					<td><?=$value['start_date']?></td>
					<td><?=$value['expiration_date']?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="9" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>