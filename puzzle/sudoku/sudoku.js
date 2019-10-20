
    /*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +                 Sudoku JS v1.05  by Michael Loesler                  +
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    + Copyright (C) 2006-11 by Michael Loesler, http//derletztekick.com    +
    +                                                                      +
    +                                                                      +
    + This program is free software; you can redistribute it and/or modify +
    + it under the terms of the GNU General Public License as published by +
    + the Free Software Foundation; either version 2 of the License, or    +
    + (at your option) any later version.                                  +
    +                                                                      +
    + This program is distributed in the hope that it will be useful,      +
    + but WITHOUT ANY WARRANTY; without even the implied warranty of       +
    + MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        +
    + GNU General Public License for more details.                         +
    +                                                                      +
    + You should have received a copy of the GNU General Public License    +
    + along with this program; if not, write to the                        +
    + Free Software Foundation, Inc.,                                      +
    + 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.            +
    +                                                                      +
     ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/		

	Array.prototype.zeros = function(o,p) {
		for (var i=0; i<o; i++){
			this[i] = new Array();
			for (var j=0; j<p; j++)
				this[i][j] = new Token(0,i,j,0);	
		}
	}

	Array.prototype.canInstert = function(T){
		var SudokuType = Math.floor(Math.sqrt(this.length));
		var check = new Array();
		for (var i=0; i<this.length; i++)
			if (this[i][T.col].number == T.number)
				return false;
			else if (this[T.row][i].number == T.number)
				return false;
			//else if (this.length-1-T.row == T.col && this[this.length-1-i][i].number == T.number)
			//	return false;	
			//else if (T.row == T.col && this[i][i].number == T.number)
			//	return false;

		
		for (var i=Math.floor(T.row/SudokuType)*SudokuType; i<Math.floor(T.row/SudokuType)*SudokuType+SudokuType; i++)
			for (var j=Math.floor(T.col/SudokuType)*SudokuType; j<Math.floor(T.col/SudokuType)*SudokuType+SudokuType; j++)
				if (this[i][j].number == T.number)
					return false;
					
		return true;	
	}
	
	Array.prototype.inArray = function(val){
		for (var i=0; i<this.length; i++)
			if (typeof(this[i]) == "object"){
				return this[i].inArray(val);
			}
			else if (this[i] == val)
					return true;
		return false;
	}
	
	Array.prototype.search = function(val){
		for (var i=0; i<this.length; i++)
			if (typeof(this[i]) == "object"){
				return this[i].search(val);
			}
			else if (this[i] == val)
					return i;
		return false;
	}
	
	Number.prototype.round = function(n){
		return Math.round(this*Math.pow(10,n))/Math.pow(10,n);
	}
	
	function trim(str){
		return str.replace(/^\s*|\s*$/g, "");
	}

	function Token(number, row, col, maxlen){
		this.number = number>maxlen?1:number;
		this.row = row;
		this.col = col;
		this.maxlen = maxlen;
		this.canceledValues = new Array();
		this.addCanceledValues = function(val){
			val = val>this.maxlen?1:val;
			this.canceledValues[this.canceledValues.length]=val;
		}
		this.isCanceledValues = function(val){
			return this.canceledValues.inArray(val);
		}
		this.isEqual = function(T){
			if (this.number == T.number && this.row == T.row && this.col == T.col)
				return true;
			return false;
		}
	}

	var Sudoku = {
		startTime : new Date(),
		counter : 1,
		highlightColor : "#ffffe0",
		hoodedFields : 0,
		SudokuType : 0,
		//Range : new Object(),
		Controller : null,
		SD : new Array(),
		solvingSD : new Array(),
		lang : window.navigator.userLanguage || window.navigator.language, //window.navigator.language.toLowerCase().indexOf("de") > -1?"de":"en",
		initSudoku : function(ST, Level){
			this.SudokuType = ST;
			var ParentEl = document.getElementById("sudoku");
			var Range = document.createElement("div");
			this.Controller = document.createElement("img");			
			window.slider[1]=new Object();
			window.slider[1].min=0;
			window.slider[1].max=8;
			window.slider[1].val=4;
			window.slider[1].onchange=function() {
				Sudoku.setLevel(Math.round(this.val));
			}
			
			this.startTime = new Date();
			
						
			Range.id = "slider01";
		
			if (Level == null) {
				this.setLevel(4);
				Range.appendChild( this.Controller );
				ParentEl.appendChild(Range);
			}	
		
			this.Table = this.createTable(this.SudokuType,this.SudokuType,0,"sudoku_panel");
			ParentEl.replaceChild(this.Table, document.getElementById("sudoku").firstChild);
			
			
			this.counter = 1;
			this.SD.zeros(this.SudokuType,this.SudokuType);
			this.solvingSD.zeros(this.SudokuType,this.SudokuType);
			this.SD.isReady = false;
			var T = new Token(Math.floor(Math.random()*this.SudokuType)+1,0,0,this.SudokuType);
			//this.createSudoku(T, true);				
			
			do {
				T = this.createSudokuStep(T);
			} while (T != null && !this.SD.isReady);
			
			this.setSudoku2Table();
		},
		
		setLevel : function(l){
			this.hoodedFields = (l*this.SudokuType>0?l*this.SudokuType:1);
		},
		
		setSudoku2Table : function (){
			function Numsort (a, b) {
				return a - b;
			}
			if (this.SD.isReady){
				var randomNumbers = new Array();

				do {
					var r = Math.floor(Math.random()*this.SudokuType*this.SudokuType)+1;
					if (!randomNumbers.inArray(r))
						randomNumbers.push(r);
				}
				while(randomNumbers.length<this.hoodedFields);
				randomNumbers.sort(Numsort);

				var k=0;
				var Rows = this.Table.getElementsByTagName("tbody")[0].rows;
				for (var i=0; i<this.SudokuType; i++){
					for (var j=0; j<this.SudokuType; j++){
						if (randomNumbers[k] == (i*this.SudokuType)+(j+1)){
							Rows[i].cells[j].firstChild.replaceData(0, Rows[i].cells[j].firstChild.nodeValue.length,"");
							var Input = document.createElement("input");
							Input.value = "";
							Input.type = "text";
							Input.title = "Wert";
							Input.maxLength = 1;
							Input.randomNumbers = randomNumbers;
							Input.id = "uid_"+this.SD[i][j].number;
							Input.Instanz = this;
							Input.sudokuIsSolved = false;
							Input.onclick   = function() { if (this.Instanz.isSolved(this.randomNumbers) && !this.sudokuIsSolved) { this.sudokuIsSolved = true; this.Instanz.getSolutionMessage(this.Instanz.startTime, this.randomNumbers);  }; };
							Input.onchange  = function() { if (this.Instanz.isSolved(this.randomNumbers) && !this.sudokuIsSolved) { this.sudokuIsSolved = true; this.Instanz.getSolutionMessage(this.Instanz.startTime, this.randomNumbers);  }; };
							Input.onkeydown = function() { if (this.Instanz.isSolved(this.randomNumbers) && !this.sudokuIsSolved) { this.sudokuIsSolved = true; this.Instanz.getSolutionMessage(this.Instanz.startTime, this.randomNumbers);  }; };
							Input.onkeyup   = function() { if (this.Instanz.isSolved(this.randomNumbers) && !this.sudokuIsSolved) { this.sudokuIsSolved = true; this.Instanz.getSolutionMessage(this.Instanz.startTime, this.randomNumbers);  }; };
							Input.onfocus   = function() { if (this.Instanz.isSolved(this.randomNumbers) && !this.sudokuIsSolved) { this.sudokuIsSolved = true; this.Instanz.getSolutionMessage(this.Instanz.startTime, this.randomNumbers);  }; };
							Rows[i].cells[j].appendChild(Input); 
							var Br = document.createElement("br");
							Rows[i].cells[j].appendChild(document.createElement("br"));
							var Notice = document.createElement("input");
							Notice.value = "";
							Notice.className = "notice";
							Notice.title = this.lang == "de"?"Notizen":"Notice";
							Notice.type = "text";
							Rows[i].cells[j].appendChild(Notice);
							Rows[i].cells[j].className += " notice"
							k++;
						}
						else {
							Rows[i].cells[j].firstChild.replaceData(0, Rows[i].cells[j].firstChild.nodeValue.length,this.SD[i][j].number);
							this.solvingSD[i][j] = new Token(this.SD[i][j].number,i,j,this.SudokuType);
						}
					}
				}
				var str = this.lang == "de"?"Zeige L\u00f6sung":"Show Solution";
				TFootRows = this.Table.getElementsByTagName("tfoot")[0].rows;
				TFootRows[0].cells[0].firstChild.replaceData(0, TFootRows[0].cells[0].firstChild.nodeValue.length, str);
				TFootRows[0].cells[0].Instanz = this;
				TFootRows[0].cells[0].onclick = function() { 
alert("42");
				};
				TFootRows[0].cells[0].title = this.lang == "de"?"Zeige mir eine m\u00f6gliche L\u00f6sung...":"Show me one solution...";
				try { TFootRows[0].cells[0].style.cursor = "pointer"; }
				catch(e){ TFootRows[0].cells[0].style.cursor = "hand"; }
				
				str = this.lang == "de"?"Neues Spiel":"New Game";
				TFootRows[0].cells[TFootRows[0].cells.length-1].firstChild.replaceData(0, TFootRows[0].cells[TFootRows[0].cells.length-1].firstChild.nodeValue.length, str);
				TFootRows[0].cells[TFootRows[0].cells.length-1].Instanz = this;
				TFootRows[0].cells[TFootRows[0].cells.length-1].onclick = function() { this.Instanz.initSudoku(this.Instanz.SudokuType, Math.abs(parseInt(this.Instanz.Controller.style.left))); };
				TFootRows[0].cells[TFootRows[0].cells.length-1].title = this.lang == "de"?"Neues Spiel starten...":"Start a new Game...";
				try { TFootRows[0].cells[TFootRows[0].cells.length-1].style.cursor = "pointer"; }
				catch(e){ TFootRows[0].cells[TFootRows[0].cells.length-1].style.cursor = "hand"; }
			}
		},

		getSolutionMessage : function(startTime, randomNumbers){
			var Rows = this.Table.getElementsByTagName("tbody")[0].rows;
			for (var i=0; i<randomNumbers.length; i++){
				var r = Math.floor((randomNumbers[i]-1)/this.SudokuType);
				var c = randomNumbers[i] - r*this.SudokuType -1;
				Rows[r].cells[c].getElementsByTagName("input")[0].readOnly = true;
			}
		
			var ss = Math.floor((new Date().getTime() - startTime.getTime())/1000);
			var mm = Math.floor(ss/60);
			var hh = Math.floor(mm/60);
			mm -= hh*60;
			ss -= mm*60;
			var time = (hh>0?hh+"h , ":"") + (hh>0||mm>0?mm+"m, ":"") + (hh>0||mm>0||ss>0?ss+"s":"")
			var messageStart = this.lang == "de"?"!!!GRATULATION!!!\n\nDu hast das SUDOKU vollstÃ¤ndig gelÃ¶st!\nBenÃ¶tigte Zeit: ":"!!!Congratulations!!!\n\nYou have completed SUDOKU!\nYour Time: ";
			var messageEnd = ".";
			window.alert( messageStart+time+messageEnd );

$.ajax({
  type: "POST",
  url: 'riddle.php',
  dataType: "json",
  data: {func: 'sudoku'},
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

		},
		
		getSolution : function(randomNumbers){
			var Rows = this.Table.getElementsByTagName("tbody")[0].rows;
			for (var i=0; i<randomNumbers.length; i++){
				var r = Math.floor((randomNumbers[i]-1)/this.SudokuType);
				var c = randomNumbers[i] - r*this.SudokuType -1;
				Rows[r].cells[c].getElementsByTagName("input")[0].value = Rows[r].cells[c].getElementsByTagName("input")[0].id.replace(/uid_/, "");
				Rows[r].cells[c].getElementsByTagName("input")[0].readOnly = true;
				Rows[r].cells[c].getElementsByTagName("input")[0].sudokuIsSolved = true;
			}
		},
			
		isSolved : function(randomNumbers){
			var Rows = this.Table.getElementsByTagName("tbody")[0].rows;
			for (var i=0; i<randomNumbers.length; i++){
				var r = Math.floor((randomNumbers[i]-1)/this.SudokuType);
				var c = randomNumbers[i] - r*this.SudokuType -1;
				if (trim(Rows[r].cells[c].getElementsByTagName("input")[0].value)=="")
					return false;
				this.solvingSD[r][c] = new Token(0,r,c,this.SudokuType);
			}
			for (var i=0; i<randomNumbers.length; i++){
				var r = Math.floor((randomNumbers[i]-1)/this.SudokuType);
				var c = randomNumbers[i] - r*this.SudokuType -1;
				var userVal = new Number(Rows[r].cells[c].getElementsByTagName("input")[0].value);				
				if (!this.solvingSD.canInstert(new Token(userVal, r, c,this.SudokuType)))
					return false;
			}
			return true;
		},

		createSudokuStep : function(T) {
			this.counter++;
			
			if (T.col == this.SD[0].length && T.row == this.SD.length-1){
				this.SD.isReady = true;
				return null;
			}
			
			if (T.col>=this.SD[0].length){
				T.col=0;
				T.row++;
			}
			else if (T.col<0){
				T.col=this.SD.length-1;
				T.row--;
			}
			
			if (!this.isStepForward){
				T = this.SD[T.row][T.col];
				this.SD[T.row][T.col] = new Token(0,T.row,T.col,this.SudokuType);
			}

			for (var l=0; l<this.SD.length; l++){
				if (this.SD.canInstert(T, this.isXSudoku) && !T.isCanceledValues(T.number)){
					if (T.row!=0&&T.coll!=0)
						T.addCanceledValues(T.number);	
					this.SD[T.row][T.col] = T;
					var StatusCell = this.Table.getElementsByTagName("tfoot")[0].rows[0].cells[this.Table.getElementsByTagName("tfoot")[0].rows[0].cells.length-1];
					StatusCell.firstChild.replaceData(0, StatusCell.firstChild.nodeValue.length, (((T.row) * this.SD.length + (T.col+1))*100/Math.pow(this.SD.length,2)).round(1)+" %" );
					this.isStepForward = true;
					return new Token(Math.floor(Math.random()*this.SD.length)+1,T.row, T.col+1,this.SudokuType); 
				}
				else {
					T.number++;
					T.number=T.number>this.SD.length?1:T.number;
				}
			}
			this.SD[T.row][T.col] = new Token(0,T.row,T.col,this.SudokuType);
			this.isStepForward = false;
			return new Token(0, T.row, T.col-1,this.SudokuType);
			
		},
		
		createSudoku : function(T,isStepForward) {
			this.counter++;
			
			if (this.counter%5==0){
				var thisObject = this;
				window.setTimeout(function() { thisObject.createSudoku(T,isStepForward); } ,1);
			}
			else {
				if (T.col == this.SD[0].length && T.row == this.SD.length-1){
					this.SD.isReady = true;
					return this.setSudoku2Table();
				}
				
				if (T.col>=this.SD[0].length){
					T.col=0;
					T.row++;
				}
				else if (T.col<0){
					T.col=this.SD.length-1;
					T.row--;
				}
				
				if (!isStepForward){
					T = this.SD[T.row][T.col];
					this.SD[T.row][T.col] = new Token(0,T.row,T.col,this.SudokuType);
				}

				for (var l=0; l<this.SD.length; l++){
					if (this.SD.canInstert(T) && !T.isCanceledValues(T.number)){
						if (T.row!=0&&T.coll!=0)
							T.addCanceledValues(T.number);
						this.SD[T.row][T.col] = T;
						var StatusCell = this.Table.getElementsByTagName("tfoot")[0].rows[0].cells[this.Table.getElementsByTagName("tfoot")[0].rows[0].cells.length-1];
						StatusCell.firstChild.replaceData(0, StatusCell.firstChild.nodeValue.length, ((T.row * this.SD.length + (T.col+1))*100/Math.pow(this.SD.length,2)).round(0)+" %" );
						return this.createSudoku(new Token(Math.floor(Math.random()*this.SD.length)+1,T.row, T.col+1,this.SudokuType),true); 
					}
					else {
						T.number++;
						T.number=T.number>this.SD.length?1:T.number;
					}
				}
				this.SD[T.row][T.col] = new Token(0,T.row,T.col,this.SudokuType);
				return this.createSudoku(new Token(0, T.row, T.col-1,this.SudokuType), false);
			}
		},
		
		// nach einer Idee von http://www.grand.lt/l/sudoku
		highlightCell : function(cell, highlight){
			var curRow = cell.parentNode;
			var Rows = curRow.parentNode.rows;
			for (var i=0; i<this.SudokuType; i++) {
				Rows[i].cells[cell.col].style.backgroundColor=(highlight)?this.highlightColor:"";
				curRow.cells[i].style.backgroundColor=(highlight)?this.highlightColor:"";
			}		
		},
	
		createTable : function(row,col,defaultValue,id){
			var Table = document.createElement("table");
			var TBody = document.createElement("tbody");
			var TFoot = document.createElement("tfoot");
			Table.appendChild(TBody);
			Table.appendChild(TFoot);
			Table.id = id;
			for (var i=0; i<row; i++){
				var Tr = document.createElement("tr");
				for (var j=0; j<col; j++){
					var Td = document.createElement("td");
					Td.row = i;
					Td.col = j;
					Td.Instanz = this;
					Td.onmouseover = function(e) { this.Instanz.highlightCell(this, true); };
					Td.onmouseout  = function(e) { this.Instanz.highlightCell(this, false); };
					
					Td.appendChild(document.createTextNode(defaultValue));
					Td.className = "";
					if (i==0)
						Td.className += " topBorder";
					if ((i+1)%Math.sqrt(this.SudokuType) == 0)
						Td.className += " bottomBorder ";
					if (j==0)
						Td.className += " leftBorder ";
					if ((j+1)%Math.sqrt(this.SudokuType) == 0)
						Td.className += " rightBorder ";
					
					Tr.appendChild(Td);
				}
				TBody.appendChild(Tr);
			}
			var Tr = document.createElement("tr");
			for (var j=0; j<3; j++){
				var Td = document.createElement("td");
				switch (j){
					case 0:
						Td.colSpan = Math.sqrt(this.SudokuType);
						Td.className = "leftFootInfo";
						Td.appendChild(document.createTextNode("GNU-GPL"));
					break;
					case 1: // do not remove this Information!
						Td.colSpan = Math.sqrt(this.SudokuType);
						Td.className = "centerFootInfo";
						Td.appendChild(document.createTextNode("SUDOKU-Puzzle"));
						try { Td.style.cursor = "pointer"; }
						catch(e){ Td.style.cursor = "hand"; }
						Td.onclick = function() { window.open("http://derletztekick.com", "_blank"); };
						Td.title = "SUDOKU-Puzzle by derletztekick.com...";
						
					break;
					case 2:
						Td.colSpan = Math.sqrt(this.SudokuType);
						Td.className = "rightFootInfo";
						Td.appendChild(document.createTextNode(defaultValue+" %"));
					break;
				}
				Tr.appendChild(Td);
			}
			TFoot.appendChild(Tr);
			return Table;
		}
	}
	
	var isDOMContentLoaded = false;
	function addContentLoadListener () {
		if (document.addEventListener) {
		var DOMContentLoadFunction = function () {
			isDOMContentLoaded = true;
			Sudoku.initSudoku(9, null);
			attachSliderEvents();
		};
		document.addEventListener("DOMContentLoaded", DOMContentLoadFunction, false);
	}
	var oldonload = (window.onload || new Function());
		window.onload = function () {
			if (!isDOMContentLoaded) {
				oldonload();
				Sudoku.initSudoku(9, null);
				attachSliderEvents();
			}
		};
	}
	addContentLoadListener();

	
	
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
Javascript Slider Control

Originally from:
  http://www.arantius.com/article/lightweight+javascript+slider+control

Copyright (c) 2006 Anthony Lieuallen, http://www.arantius.com/

Permission is hereby granted, free of charge, to any person obtaining a copy of 
this software and associated documentation files (the "Software"), to deal in 
the Software without restriction, including without limitation the rights to 
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of 
the Software, and to permit persons to whom the Software is furnished to do so, 
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS 
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER 
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
var slider=new Array();
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
//add event function from http://www.dynarch.com/projects/calendar/
function addAnEvent(el, evname, func) {
    if (el.attachEvent) { // IE
        el.attachEvent("on" + evname, func);
    } else if (el.addEventListener) { // Gecko / W3C
        el.addEventListener(evname, func, true);
    } else {
        el["on" + evname] = func;
    }
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function drawSliderByVal(slider) {
	var knob=slider.getElementsByTagName('img')[0];
	var p=(slider.val-slider.min)/(slider.max-slider.min);
	//var x=(slider.scrollWidth-9)*p;
	var x=(slider.offsetWidth-9)*p;
	knob.style.left=x+"px";
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function setSliderByClientX(slider, clientX) {
	//var p=(clientX-slider.offsetLeft-5)/(slider.scrollWidth-9);
	var p=(clientX-slider.offsetLeft-5)/(slider.offsetWidth-9);
	slider.val=(slider.max-slider.min)*p + slider.min;
	if (slider.val>slider.max) slider.val=slider.max;
	if (slider.val<slider.min) slider.val=slider.min;

	drawSliderByVal(slider);
	slider.onchange(slider.val, slider.num);
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function sliderClick(e) {
	var el=sliderFromEvent(e);
	if (!el) return;

	setSliderByClientX(el, e.clientX);
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function sliderMouseMove(e) {
	var el=sliderFromEvent(e);
	if (!el) return;
	if (activeSlider<0) return;

	setSliderByClientX(el, e.clientX);
	stopEvent(e);
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function sliderFromEvent(e) {
	if (!e && window.event) e=window.event;
	if (!e) return false;

	var el;
	if (e.target) el=e.target;
	if (e.srcElement) el=e.srcElement;

	if (!el.id || !el.id.match(/slider\d+/)) el=el.parentNode;
	if (!el) return false;
	if (!el.id || !el.id.match(/slider\d+/)) return false;

	return el;
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function attachSliderEvents() {
	var divs=document.getElementsByTagName('div');
	var divNum;
	for(var i=0; i<divs.length; i++) {
		if (divNum=divs[i].id.match(/\bslider(\d+)\b/)) {
			// set initial properties
			divNum=parseInt(divNum[1]);
			divs[i].min=slider[divNum].min;
			divs[i].max=slider[divNum].max;
			divs[i].val=slider[divNum].val;
			divs[i].onchange=slider[divNum].onchange;
			divs[i].num=divNum;
			// and make sure the display matches
			drawSliderByVal(divs[i]);
			divs[i].onchange(divs[i].val, divNum);

			addAnEvent(divs[i], 'mousedown', function(e){
				sliderClick(e);
				var el=sliderFromEvent(e);
				if (!el) return;
				activeSlider=el.num;
				stopEvent(e);
			});
			addAnEvent(divs[i], 'mouseup', function(e){
				activeSlider=-1;
				stopEvent(e);
			});
		}
	}
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
//borrowed from prototype: http://prototype.conio.net/
function stopEvent(event) {
	if (event.preventDefault) {
		event.preventDefault();
		event.stopPropagation();
	} else {
		event.returnValue=false;
		event.cancelBubble=true;
	}
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
//addAnEvent(window, 'load', attachSliderEvents);
addAnEvent(document, 'mousemove', sliderMouseMove);
var activeSlider=-1;
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
