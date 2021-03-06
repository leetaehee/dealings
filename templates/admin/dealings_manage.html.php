<p><h3>[<?=TITLE_ADMIN_DEALINGS_STATUS?>]</h3></p>
<p><h5>-거래완료를 하실 경우에는 취소 하실수 없습니다. 참고하세요.</h5></p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 10%;">
		<col style="width: 30%;">
		<col style="width: 14%;">
		<col style="width: 13%;">
		<col style="width: 11%;">
		<col style="width: 8%;">
		<col style="width: 8%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>분류</th>
			<th>제목</th>
			<th>작성자</th>
			<th>거래자</th>
			<th>거래일자</th>
			<th>거래금액</th>
			<th>거래상태</th>
		</tr>
	</thead>
	<tbody>
		<?php if($dealingsUserDataCount > 0): ?>
			<?php foreach($dealingsUserData as $key => $value): ?>
				<tr>
					<td><?=$value['seq']?></td>
					<td><?=$value['item_name']?></td>
					<td>[<?=$value['dealings_type']?>]<br><?=$value['dealings_subject']?></td>
					<td><?=$value['name']?><br>(<?=$value['id']?>)</td>
					<td><?=$value['dealings_user_name']?></td>
					<td><?=$value['dealings_date']?></td>
					<td><?=$value['dealings_mileage']?></td>
					<td><?=$value['dealings_status_name']?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="8" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>