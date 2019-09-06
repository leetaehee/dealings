<p><h3>[회원현황]</h3></p>
<table class="table charge-table-width">
	<colgroup>
		<col style="width: 7%;">
		<col style="width: 11%;">
		<col style="width: 15%;">
		<col style="width: 20%;">
		<col style="width: 16%;">
		<col style="width: 10%;">
		<col style="width: 11%;">
		<col style="width: 11%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>이름</th>
			<th>ID</th>
			<th>이메일</th>
			<th>연락처</th>
			<th>성별</th>
			<th>가입일</th>
			<th>마일리지</th>
		</tr>
	</thead>
	<tbody>
		<?php if($rocordCount > 0): ?>
			<?php foreach($memberList as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=setDecrypt($value['name'])?></td>
					<td><?=$value['id']?></td>
					<td><?=setDecrypt($value['email'])?></td>
					<td><?=setDecrypt($value['phone'])?></td>
					<td><?=$value['sex_name']?></td>
					<td><?=$value['join_approval_date']?></td>
					<td><?=$value['mileage']?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="8" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>