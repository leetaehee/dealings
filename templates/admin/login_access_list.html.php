<p><h3>[로그인 접속기록]</h3></p>
<p>(기본값은 "오늘"입니다.)</p>
<table class="table login-table-width">
	<colgroup>
		<col style="width: 10%;">
		<col style="width: 25%;">
		<col style="width: 25;">
		<col style="width: 40%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>접속IP</th>
			<th>접속일자</th>
			<th>접속시각</th>
		</tr>
	</thead>
	<tbody>
		<?php if($rocordCount > 0): ?>
			<?php foreach($adminLoginAccessList as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=setDecrypt($value['access_ip'])?></td>
					<td><?=$value['access_date']?></td>
					<td><?=$value['access_datetime']?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="4" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>