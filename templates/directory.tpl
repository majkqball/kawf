%begin [dir]
<table class="basic">
    <tr><th>Forum</th><th>posts</th></tr>
%begin [row]
    <tr class="row%[r]">
	<td><a href="/%[row(shortname)]">%[row(name)]</a></td>
	<td>%[count]</td>
    </tr>
%end [row]
</table>
%end [dir]