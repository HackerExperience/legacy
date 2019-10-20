/** @jsx React.DOM */

var boards = [
  [
    [1,0,0,1,1],
    [0,1,0,1,0],
    [1,0,1,0,1  ],
    [0,1,0,1,0],
    [1,0,0,1,1]
  ]
];

var TransitionGroup = React.addons.TransitionGroup;
var classSet = React.addons.classSet;

var Switch = React.createClass({
  render: function() {
    // got the props from parent (LightsOut)
    var classes = {
      'switch': true,
      'switch-done': this.props.done,
      'switch-on': this.props.isOn,
      'switch-off': !this.props.isOn
    }

    return <div className={classSet(classes)} onClick={this.props.onClick} />
  }
});

var LightsOut = React.createClass({
  getInitialState: function() {
    // component's internal value(s)
    return {
      board: this.getNewRandomBoard(),
      done: false
    };
  },

  getNewRandomBoard: function() {
    // clone a board
    return boards[Math.floor(Math.random() * boards.length)].map(function(row) {
      return row.map(function(cell) {
        return cell;
      });
    });
  },

  handleReset: function() {
    // updating the state auto re-renders the component UI
    this.setState({
      board: this.getNewRandomBoard(),
      done: false
    });
  },

  handleSwitchClick: function(i, j) {
    var board = this.state.board;
    var lastCellIndex = board.length - 1;
    // flip current and ajacent switches
    board[i][j] = !board[i][j];
    if (i !== 0) board[i - 1][j] = !board[i - 1][j];
    if (i !== board[i].length - 1) board[i + 1][j] = !board[i + 1][j];
    if (j !== 0) board[i][j - 1] = !board[i][j - 1];
    if (j !== board.length - 1) board[i][j + 1] = !board[i][j + 1];

    var done = this.state.board.every(function(row) {
      return row.every(function(cell) {
        return !!cell;
      });
    });
    // setState is asynchronous. Pass a callback that verifies if all the lights
    // are on; if so, create new game
    this.setState({board: this.state.board, done: done}, function() {
      if (done) {

$.ajax({
  type: "POST",
  url: 'riddle.php',
  dataType: "json",
  data: {func: 'lightsout'},
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
});

      }
    }.bind(this));
  },

  render: function() {
    return (
      <div>
        {
          this.state.board.map(function(row, i) {
            return (
              // `TransitionGroup` defaults to a `span` wrapper. We want a `div`
              <TransitionGroup
                transitionName="switch"
                component={React.DOM.div}
              >
                {
                  row.map(function(cell, j) {
                    // 5x5 swiches. Pass some props to each one
                    return (
                      <Switch
                        isOn={!!cell}
                        done={this.state.done}
                        onClick={this.handleSwitchClick.bind(this, i, j)}
                      />
                    )
                  }, this)
                }
              </TransitionGroup>
            )
          }, this)
        }
      </div>
    );
  }
});

React.renderComponent(<LightsOut />, document.getElementById('game'))
