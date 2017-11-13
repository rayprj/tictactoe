<?php
/**
 * bootstrap class for this app
 **/
class Game implements MoveInterface
{
    private $board;
    private $algorithm;
    private $config;
    /**
     * @param Array           $board
     * @param Algorithm       $ag
     * @param GameConfig      $gc
     * @param BoardEvaluation $be
     */
    public function __construct(array $board, Algorithm $a, GameConfig $gc, BoardEvaluator $be)
    {
        $bv = new BoardValidator($board, $gc);
        $bv->isValid();

        $this->board     = $board;
        $this->algorithm = $a;
        $this->config    = $gc;
        $this->boardEvaluator = $be;
    }
    /**
     * to place a next move
     * @return integer
     */
    public function getNextMove()
    {
        return $this->makeMove(
            $this->board,
            $this->config->get('player')
        );
    }
   /**
     * Makes a move using the $boardState
     *
     * @param array  $boardState Current board state
     * @param string $playerUnit Player unit representation
     * @return array
     **/
    public function makeMove($boardState, $playerUnit = 'x')
    {
        $bestVal = -1000;
        $blocks  = $this->config->get('blocks');
        $space   = $this->config->get('space');
        $bestMove = [-1, -1, $playerUnit];
        for ($i = 0; $i<$blocks; $i++) {
            for ($j = 0; $j<$blocks; $j++) {
                if ($boardState[$i][$j] == $space) {
                    $boardState[$i][$j] = $this->config->get('player');
                    $moveVal = $this->algorithm->minimax($boardState, 0, false);
                    $boardState[$i][$j] = $space;//make board empty
                    if ($moveVal > $bestVal) {
                        $bestMove[1] = $i; //y
                        $bestMove[0] = $j; //x
                        $bestVal = $moveVal;
                    }
                }
            }
        }
        $boardState[$bestMove[1]][$bestMove[0]] = $bestMove[2];
        $bestMove[3] = $this->boardEvaluator->strikableBy($boardState);
        return $bestMove;
    }
}
