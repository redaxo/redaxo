<?php
/**
* Class to execute simple mathematical calculations with expression in infix notation
* Curently allows simple algebraic binary operations:
* - + (addition)
* - - (substraction)
* - * (multiplication)
* - / (division)
* - ^ (exponential)
* It also allows to use braces and watchs for priority of operations
* @author Vladimir Shugaev <vladimir.shugaev@junvo.com>
* @copyright Vladimir Shugaev <vladimir.shugaev@junvo.com>
* @license GNU GPL v2 or GNU LGPL
**/

class SimpleCalc{

	/**
	* Priority of operations
	**/
	var $priority=array(
		'^'=>3,
		'*'=>2,
		'/'=>2,
		'+'=>1,
		'-'=>1
	);

	/**
	* Prepares expression for calculations and calculates it with SimpleCalc::exec. Returns the result of calculations
	* @param string $expression - mathematical expression in infix notation
	* @return float - result of calculation of $expression
	**/
	public function calculate($expression){
		$expression=str_replace(' ','',$expression);
		return $this->exec($expression);
	}

	/**
	* Recursively parses $expression and returns the result
	* @param string $expression - mathematical expression in infix notation without spaces
	* @return float - result of calculation of $expression
	**/
	private function exec($expression){
		if (is_numeric($expression))
			return ($expression);
		else{
			$parsed=$this->parseExpression($expression);
			$parsed[0]=empty($parsed[0])?0:$parsed[0];
			switch ($parsed['operator']){
				case '+':
					$r = $this->exec($parsed[0])+$this->exec($parsed[1]); break;
				case '-':
					$r = $this->exec($parsed[0])-$this->exec($parsed[1]); break;
				case '*':
					$r = $this->exec($parsed[0])*$this->exec($parsed[1]); break;
				case '/':
					$r = $this->exec($parsed[0])/$this->exec($parsed[1]); break;
				case '^':
					$r = pow($this->exec($parsed[0]),$this->exec($parsed[1])); break;
				case '(':
					$r = $this->exec($parsed[0]); break;
			}
			return $r;
		}
	}

	/**
	* Explodes $expression by the operation with the lowest priority
	* @param string $expression mathematical expression in infix notation without spaces
	* @return array Array of operands with numerical indexes and operator with 'operator' key
	**/
	private function parseExpression($expression){
		//state 0 for seeking operatorors
		//state 1 for skeeping braces
		$state=0;

		//ammount of opened and not closed braces to current position
		$braces=0;

		//operators position and priority
		$operatorsPositions=array();
		$operators=array_keys($this->priority);

		$len=strlen($expression);
		for ($position=0; $position<$len; $position++){
		$char = $expression{$position};
			switch ($state){
				case 0:
					if (array_search($char, $operators)!==false){
						if (($char=='+'||$char=='-')&&($expression{$position-1}=='E')) //ignore + and - in exponential like 6.6742E-11
							continue;
						$operatorsPositions[$position]=$this->priority[$char];
					}
					if ($char =='('){
						$braces++;
						$state=1;
					}
					break;
				case 1:
					if ($char=='(')
						$braces++;
					if ($char==')')
						$braces--;
					if ($braces==0)
						$state=0;
					break;
			}
		}

		if (count($operatorsPositions)){
			$minPriority=min (array_values($operatorsPositions));
			$dividePositions=array_keys($operatorsPositions, $minPriority);
			$dividePosition=max ($dividePositions);
			$operator=$expression[$dividePosition];

			$result=array(
				'operator'=>$operator,
				substr($expression,0,$dividePosition),
				substr($expression,$dividePosition+1)
			);
			return $result;
		}

		else{
			$result=array(
				'operator'=>'(',
				substr($expression,1,-1)
			);
			return $result;
		}
	}
}

?>
