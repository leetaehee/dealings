<p>
	<h3>[<?=TITLE_COUPON_USEAGE?>]</h3>
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 8%;">
		<col style="width: 9%;">
		<col style="width: 24%;">
		<col style="width: 25%;">
		<col style="width: 8%;">
		<col style="width: 10%;">
		<col style="width: 10%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>구분</th>
			<th>회원이름</th>
			<th>쿠폰명</th>
			<th>상품명</th>
			<th>할인율</th>
			<th>판매금액</th>
			<th>거래금액</th>
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
					<td><?=number_format($value['discount_rate'])?>%</td>
					<td><?=number_format($value['item_money'])?></td>
					<td>-</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="8" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>