/*
* Credit to http://forkk.net/
* (Original one is quite unbeatable so we commented some AI lines)
*/

var isIE;
var canvas;
var turnDisplay;
var autoAICheck;

var bgColor;
var fgColor;

var cols;
var rows;

var marks;
var xTurn;

function OnInit()
{

  var gameEnd = false;
  xTurn = true;

  cols = 3;
  rows = 3;

  canvas = document.getElementById("TTT_Canvas");

  if (!canvas)
    return false;

  if (canvas.dataset["bgcolor"])
  {
    bgColor = canvas.dataset["bgcolor"];
  }
  else
  {
    bgColor = "#FFFFFF";
  }

  if (canvas.dataset["fgcolor"])
  {
    fgColor = canvas.dataset["fgcolor"];
  }
  else
  {
    fgColor = "#000000";
  }

  canvas.onclick = OnClick;

  turnDisplay = document.getElementById("TTT_TurnDisplay");

  autoAICheck = document.getElementById("TTT_AutoAICheck");
  if (autoAICheck)
    autoAICheck.checked = true;

  isIE = document.all ? true : false;

  canvas.onmousemove = GetMousePos;

  ResetBoard();
}

function Update()
{
  if (turnDisplay)
    turnDisplay.innerHTML = xTurn ? "X's Turn" : "O's Turn";

  Redraw();
  WinCheck();

  if (!xTurn && (!autoAICheck || autoAICheck.checked))
    RunAI(2);
}

function ResetBoard()
{
  marks =
  [
    [ 0, 0, 0 ],
    [ 0, 0, 0 ],
    [ 0, 0, 0 ]
  ];

  Update(true);
}

function Redraw()
{
  var ctx = canvas.getContext("2d");
  ctx.fillStyle = bgColor;
  ctx.strokeStyle = bgColor;
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = fgColor;
  ctx.strokeStyle = fgColor;
  DrawGrid();
}

function DrawGrid()
{
  var ctx = canvas.getContext("2d");

  var colSize = canvas.width / cols;
  var rowSize = canvas.height / rows;

  // Draw horizontal lines
  for (var x = 1; x < cols; x++)
  {
    ctx.beginPath();
    ctx.moveTo(x * colSize, 0);
    ctx.lineTo(x * colSize, canvas.height);
    ctx.stroke();
    ctx.closePath();
  }

  // Draw vertical lines
  for (var y = 1; y < rows; y++)
  {
    ctx.beginPath();
    ctx.moveTo(0, y * rowSize);
    ctx.lineTo(canvas.width, y * rowSize);
    ctx.stroke();
    ctx.closePath();
  }

  // Draw markers
  for (var x = 0; x < cols; x++)
  {
    for (var y = 0; y < rows; y++)
    {
      if (marks[x][y] == 1)
      {
        DrawX(x * colSize, y * rowSize, colSize, rowSize, ctx);
      }
      else if (marks[x][y] == 2)
      {
        DrawO(x * colSize, y * rowSize, colSize, rowSize, ctx);
      }
    }
  }
}

function DrawX(x, y, w, h, ctx)
{
  ctx.beginPath();
  ctx.moveTo(x, y);
  ctx.lineTo(x + w, y + h);
  ctx.stroke();
  ctx.closePath();

  ctx.beginPath();
  ctx.moveTo(x + w, y);
  ctx.lineTo(x, y + h);
  ctx.stroke();
  ctx.closePath();
}

function DrawO(x, y, w, h, ctx)
{
  ctx.beginPath();
  ctx.arc(x + w / 2, y + h / 2, w / 2 - 5, 0 , Math.PI*2, true);
  ctx.stroke();
  ctx.closePath();
}

function OnClick(e)
{

  var x = posX;
  var y = posY;

  var cellX = 0;
  var cellY = 0;

  var colSize = canvas.width / cols;
  var rowSize = canvas.height / rows;

  for (var i = 0; i < cols; i++)
  {
     if (x >= i * colSize)
     {
      cellX = i;
     }
  }

  for (var i = 0; i < rows; i++)
  {
     if (y >= i * rowSize)
     {
      cellY = i;
     }
  }

  PlaceMark(cellX, cellY);
}

function PlaceMark(cellX, cellY)
{
  if (marks[cellX][cellY] == 0)
  {
    if (xTurn)
      marks[cellX][cellY] = 1;
    else
      marks[cellX][cellY] = 2;

    xTurn = !xTurn;
  }

  Update();
}

function WinCheck()
{
  // Check columns
  for (var col = 0; col < cols; col++)
  {
    var lastMark = marks[col][0];
    for (var row = 0; row < rows; row++)
    {
      if (marks[col][row] != lastMark)
      {
        lastMark = 0;
        break;
      }
    }
    if (lastMark != 0)
      OnWin(lastMark);
  }

  // Check rows
  for (var row = 0; row < rows; row++)
  {
    var lastMark = marks[0][row];
    for (var col = 0; col < cols; col++)
    {
      if (marks[col][row] != lastMark)
      {
        lastMark = 0;
        break;
      }
    }
    if (lastMark != 0)
      OnWin(lastMark);
  }

  // Check diagonals
  if (marks[1][1] != 0 && marks[1][1] == marks[0][0] && marks[1][1] == marks[2][2])
  {
    OnWin(marks[1][1]);
  }
  if (marks[1][1] != 0 && marks[1][1] == marks[0][2] && marks[1][1] == marks[2][0])
  {
    OnWin(marks[1][1]);
  }

  // Check for a draw
  var hasBlankSpace = false;
  for (var col = 0; col < cols; col++)
  {
    for (var row = 0; row < rows; row++)
    {
      if (marks[col][row] == 0)
      {
        hasBlankSpace = true;
        break;
      }
    }

    if (hasBlankSpace)
      break;
  }
  if (!hasBlankSpace)
  {
    OnWin(0);
  }
}

function OnWin(winner)
{

  if($('#puzzle-isSolved').attr('value') == 'true'){
    ResetBoard();
    return;
  }

  $.ajax({
      type: "POST",
      url: 'riddle.php',
      dataType: "json",
      data: {func: 'tictactoe', status: winner},
       success:function(data) {
        if(data.status == 'OK'){
          result = $.parseJSON(data.msg);
          $('#puzzle-header').html(result[0].header);
          $('#puzzle-status').html(result[0].result);
          $('#puzzle-next').html(result[0].next);
          $('#puzzle-isSolved').attr('value', result[0].isSolved);
          $('#puzzle-solve').hide();
        }
      }
  })

  ResetBoard();
  //setTimeout(function(){ResetBoard();}, 500);
}

function RunAI(aiPlayer)
{
  var move = GetAIMove(aiPlayer);
  PlaceMark(move[0], move[1]);
}

function GetAIMove(aiPlayer)
{
  var enemy = aiPlayer == 1 ? 2 : 1;

  // Template
  // if (marks[][] == aiPlayer && marks[][] == aiPlayer && marks[][] == 0) return [, ];

  // If we can win on this turn, do it.

  // Check rows
  for (var row = 0; row < rows; row++)
  {
    if (marks[0][row] == 0 && marks[1][row] == aiPlayer && marks[2][row] == aiPlayer) return [0, row];
    if (marks[0][row] == aiPlayer && marks[1][row] == 0 && marks[2][row] == aiPlayer) return [1, row];
    if (marks[0][row] == aiPlayer && marks[1][row] == aiPlayer && marks[2][row] == 0) return [2, row];
  }

  // Check columns
  for (var col = 0; col < cols; col++)
  {
    if (marks[col][0] == 0 && marks[col][1] == aiPlayer && marks[col][2] == aiPlayer) return [col, 0];
    if (marks[col][0] == aiPlayer && marks[col][1] == 0 && marks[col][2] == aiPlayer) return [col, 1];
    if (marks[col][0] == aiPlayer && marks[col][1] == aiPlayer && marks[col][2] == 0) return [col, 2];
  }

  // Check diagonals
  if (marks[0][0] == 0 && marks[1][1] == aiPlayer && marks[2][2] == aiPlayer) return [0, 0];
  if (marks[0][0] == aiPlayer && marks[1][1] == 0 && marks[2][2] == aiPlayer) return [1, 1];
  if (marks[0][0] == aiPlayer && marks[1][1] == aiPlayer && marks[2][2] == 0) return [2, 2];

  if (marks[2][0] == 0 && marks[1][1] == aiPlayer && marks[0][2] == aiPlayer) return [2, 0];
  if (marks[2][0] == aiPlayer && marks[1][1] == 0 && marks[0][2] == aiPlayer) return [1, 1];
  if (marks[2][0] == aiPlayer && marks[1][1] == aiPlayer && marks[0][2] == 0) return [0, 2];


  // If no winning moves are found, attempt to block opponent wherever possible.

  // Check rows
  for (var row = 0; row < rows; row++)
  {
    if (marks[0][row] == 0 && marks[1][row] == enemy && marks[2][row] == enemy) return [0, row];
    if (marks[0][row] == enemy && marks[1][row] == 0 && marks[2][row] == enemy) return [1, row];
    if (marks[0][row] == enemy && marks[1][row] == enemy && marks[2][row] == 0) return [2, row];
  }

  // Check columns
  for (var col = 0; col < cols; col++)
  {
    if (marks[col][0] == 0 && marks[col][1] == enemy && marks[col][2] == enemy) return [col, 0];
    if (marks[col][0] == enemy && marks[col][1] == 0 && marks[col][2] == enemy) return [col, 1];
    if (marks[col][0] == enemy && marks[col][1] == enemy && marks[col][2] == 0) return [col, 2];
  }

  // Check diagonals
  if (marks[0][0] == 0 && marks[1][1] == enemy && marks[2][2] == enemy) return [0, 0];
  if (marks[0][0] == enemy && marks[1][1] == 0 && marks[2][2] == enemy) return [1, 1];
  if (marks[0][0] == enemy && marks[1][1] == enemy && marks[2][2] == 0) return [2, 2];

  if (marks[2][0] == 0 && marks[1][1] == enemy && marks[0][2] == enemy) return [2, 0];
  if (marks[2][0] == enemy && marks[1][1] == 0 && marks[0][2] == enemy) return [1, 1];
  if (marks[2][0] == enemy && marks[1][1] == enemy && marks[0][2] == 0) return [0, 2];


  // Now do some funky stuff to prevent certain strategies from beating the AI.
/*
  // Both corners
  if (marks[2][2] == enemy && marks[0][0] == enemy && marks[1][0] == 0) return [1, 0];
  if (marks[2][0] == enemy && marks[0][2] == enemy && marks[1][0] == 0) return [1, 0];


  if (marks[0][1] == enemy && marks[2][2] == enemy && marks[1][0] == 0) return [1, 0];
  if (marks[0][2] == enemy && marks[2][1] == enemy && marks[1][0] == 0) return [1, 0];

  if (marks[2][0] == enemy && marks[1][2] == enemy && marks[0][1] == 0) return [0, 1];
  if (marks[1][0] == enemy && marks[2][2] == enemy && marks[0][1] == 0) return [0, 1];

  if (marks[0][1] == enemy && marks[2][0] == enemy && marks[1][2] == 0) return [1, 2];
  if (marks[0][0] == enemy && ma3ks[2][1] == enemy && marks[1][2] == 0) return [1, 2];

  if (marks[1][0] == enemy && marks[0][2] == enemy && marks[2][1] == 0) return [2, 1];
  if (marks[0][0] == enemy && marks[1][2] == enemy && marks[2][1] == 0) return [2, 1];

  if (marks[1][1] == enemy && marks[2][2] == enemy && marks[2][0] == 0) return [2, 0];
  
  if (marks[1][2] == enemy && marks[2][1] == enemy && marks[0][2] == 0) return [0, 2];

  // If the opponent takes a corner or an edge, take the middle
  if (marks[1][1] == 0 && (marks[0][0] == enemy || marks[2][0] == enemy || marks[0][2] == enemy || marks[2][2] == enemy || marks[1][0] == enemy || marks[0][1] == enemy || marks[1][2] == enemy || marks[2][1] == enemy)) return [1, 1];
*/
  // As a last resort, pick the first available square
  for (var col = 0; col < cols; col++)
  {
    for (var row = 0; row < rows; row++)
    {
      if (marks[row][col] == 0)
        return [row, col];
    }
  }

}

function GetMousePos(e)
{
  var _x;
  var _y;

  if (e.offsetX)
  {
    _x = e.offsetX;
    _y = e.offsetY;
  }
  else if (e.layerX)
  {
    _x = e.layerX;
    _y = e.layerY;
  }

  posX = _x;
  posY = _y;
  return true;
}