<script src="js/jquery.min.js"></script>
<script src="puzzle/minesweeper/minesweeper.js"></script>
<link type="text/css" rel="stylesheet" href="puzzle/minesweeper/style.css">
<style>td{border:1px solid #ccc;padding:7px 10px;width:15px;text-align:center;cursor:pointer;font:bold 16px arial;background-color:#fffffa;}td.mark{color:red;background-color:#ccc;}td.bomb{color:red;}.lost td.bomb{color:yellow;background-color:black}</style>

<div id="status" class="alert" style="display:none;"></div>
<span id="newgame" class="btn btn-info" style="display:none; margin-bottom: 10px;">Try again</span><br/>
<table style="display: inline-block; text-align: center;" id="minesweeper"></table>
