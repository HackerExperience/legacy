/*
  File: main.js
  Abstract: JavaScript file for TicTacToe sample.
  
  Version: 1.0
  
  Disclaimer: IMPORTANT:  This Apple software is supplied to you by 
  Apple Inc. ("Apple") in consideration of your agreement to the
  following terms, and your use, installation, modification or
  redistribution of this Apple software constitutes acceptance of these
  terms.  If you do not agree with these terms, please do not use,
  install, modify or redistribute this Apple software.

  In consideration of your agreement to abide by the following terms, and
  subject to these terms, Apple grants you a personal, non-exclusive
  license, under Apple's copyrights in this original Apple software (the
  "Apple Software"), to use, reproduce, modify and redistribute the Apple
  Software, with or without modifications, in source and/or binary forms;
  provided that if you redistribute the Apple Software in its entirety and
  without modifications, you must retain this notice and the following
  text and disclaimers in all such redistributions of the Apple Software. 
  Neither the name, trademarks, service marks or logos of Apple Inc. 
  may be used to endorse or promote products derived from the Apple
  Software without specific prior written permission from Apple.  Except
  as expressly stated in this notice, no other rights or licenses, express
  or implied, are granted by Apple herein, including but not limited to
  any patent rights that may be infringed by your derivative works or by
  other works in which the Apple Software may be incorporated.

  The Apple Software is provided by Apple on an "AS IS" basis.  APPLE
  MAKES NO WARRANTIES, EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION
  THE IMPLIED WARRANTIES OF NON-INFRINGEMENT, MERCHANTABILITY AND FITNESS
  FOR A PARTICULAR PURPOSE, REGARDING THE APPLE SOFTWARE OR ITS USE AND
  OPERATION ALONE OR IN COMBINATION WITH YOUR PRODUCTS.

  IN NO EVENT SHALL APPLE BE LIABLE FOR ANY SPECIAL, INDIRECT, INCIDENTAL
  OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
  INTERRUPTION) ARISING IN ANY WAY OUT OF THE USE, REPRODUCTION,
  MODIFICATION AND/OR DISTRIBUTION OF THE APPLE SOFTWARE, HOWEVER CAUSED
  AND WHETHER UNDER THEORY OF CONTRACT, TORT (INCLUDING NEGLIGENCE),
  STRICT LIABILITY OR OTHERWISE, EVEN IF APPLE HAS BEEN ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
  
  Copyright (C) 2010 Apple Inc. All Rights Reserved.
*/

/***************************************/
/*         intial setup                */
/***************************************/
var board = new Array(9);

function init() {
  /* use touch events if they're supported, otherwise use mouse events */
  var down = "mousedown"; var up = "mouseup"; 
  if ('createTouch' in document) { down = "touchstart"; up ="touchend"; }
  
  /* add event listeners */
  document.querySelector("input.button").addEventListener(up, newGame, false);
  var squares = document.getElementsByTagName("td");
  for (var s = 0; s < squares.length; s++) {
    squares[s].addEventListener(down, function(evt){squareSelected(evt, getCurrentPlayer());}, false);
  }
  
  /* create the board and set the initial player */
  createBoard();
  setInitialPlayer();
}


/****************************************************************************************/
/* creating or restoring a game board, adding Xs and Os to the board, saving game state */
/****************************************************************************************/
function createBoard() {

  /* create a board from the stored version, if a stored version exists */
  if (window.localStorage && localStorage.getItem('tic-tac-toe-board')) {
    
    /* parse the string that represents our playing board to an array */
    board = (JSON.parse(localStorage.getItem('tic-tac-toe-board')));
    for (var i = 0; i < board.length; i++) {
      if (board[i] != "") {
        fillSquareWithMarker(document.getElementById(i), board[i]);
      }
    }
  }
  /* otherwise, create a clean board */
  else {  
    for (var i = 0; i < board.length; i++) {
      board[i] = "";                               
      document.getElementById(i).innerHTML = "";
    }
  }
}

/*** call this function whenever a square is clicked or tapped ***/
function squareSelected(evt, currentPlayer) {
  var square = evt.target;
  /* check to see if the square already contains an X or O marker */
  if (square.className.match(/marker/)) {
    alert("Sorry, that space is taken!  Please choose another square.");
    return;
  }
  /* if not already marked, mark the square, update the array that tracks our board, check for a winner, and switch players */
  else {
    fillSquareWithMarker(square, currentPlayer);
    updateBoard(square.id, currentPlayer);
    checkForWinner();
    switchPlayers(); 
  }
}

/*** create an X or O div and append it to the square ***/
function fillSquareWithMarker(square, player) {
  var marker = document.createElement('div');
  /* set the class name on the new div to X-marker or O-marker, depending on the current player */
  marker.className = player + "-marker";
  square.appendChild(marker);
}

/*** update our array which tracks the state of the board, and write the current state to local storage ***/
function updateBoard(index, marker) {
  board[index] = marker;
  
  /* HTML5 localStorage only allows storage of strings - convert our array to a string */
  var boardstring = JSON.stringify(board);

  /* store this string to localStorage, along with the last player who marked a square */
  localStorage.setItem('tic-tac-toe-board', boardstring); 
  localStorage.setItem('last-player', getCurrentPlayer());
}


/***********************************************************************************/
/* checking for and declaring a winner, after a square has been marked with X or O */
/***********************************************************************************/
/* Our Tic Tac Toe board, an array:
  0 1 2
  3 4 5
  6 7 8
*/
function declareWinner() {
  if (confirm("We have a winner!  New game?")) {
    newGame();
  }
}

function weHaveAWinner(a, b, c) {
  if ((board[a] === board[b]) && (board[b] === board[c]) && (board[a] != "" || board[b] != "" || board[c] != "")) {
    setTimeout(declareWinner(), 100);
    return true;
  }
  else
    return false;
}

function checkForWinner() {
  /* check rows */
  var a = 0; var b = 1; var c = 2;
  while (c < board.length) {
    if (weHaveAWinner(a, b, c)) {
      return;
    }
    a+=3; b+=3; c+=3;
  }
    
  /* check columns */
  a = 0; b = 3; c = 6;
  while (c < board.length) {
    if (weHaveAWinner(a, b, c)) {
      return;
    }
    a+=1; b+=1; c+=1;
  }

  /* check diagonal right */
  if (weHaveAWinner(0, 4, 8)) {
    return;
  }
  /* check diagonal left */
  if (weHaveAWinner(2, 4, 6)) {
    return;
  }
  
  /* if there's no winner but the board is full, ask the user if they want to start a new game */
  if (!JSON.stringify(board).match(/,"",/)) {
    if (confirm("It's a draw. New game?")) {
      newGame();
    }
  }
}


/****************************************************************************************/
/* utilities for getting the current player, switching players, and creating a new game */
/****************************************************************************************/
function getCurrentPlayer() {
  return document.querySelector(".current-player").id;
}

/* set the initial player, when starting a whole new game or restoring the game state when the page is revisited */
function setInitialPlayer() {
  var playerX = document.getElementById("X");
  var playerO = document.getElementById("O");
  playerX.className = "";
  playerO.className = "";
    
  /* if there's no localStorage, or no last-player stored in localStorage, always set the first player to X by default */
  if (!window.localStorage || !localStorage.getItem('last-player')) {
    playerX.className = "current-player";
    return;
  } 

  var lastPlayer = localStorage.getItem('last-player');  
  if (lastPlayer == 'X') {
    playerO.className = "current-player";
  }
  else {
    playerX.className = "current-player";
  }
}

function switchPlayers() {
  var playerX = document.getElementById("X");
  var playerO = document.getElementById("O");
  
  if (playerX.className.match(/current-player/)) {
    playerO.className = "current-player";
    playerX.className = "";
  }
  else {
    playerX.className = "current-player";
    playerO.className = "";
  }
}

function newGame() {  
  /* clear the currently stored game out of local storage */
  localStorage.removeItem('tic-tac-toe-board');
  localStorage.removeItem('last-player');
  
  /* create a new game */
  createBoard();
}




