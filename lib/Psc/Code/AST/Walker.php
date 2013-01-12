<?php

namespace Psc\Code\AST;

use Psc\Data\Type\Type;
use Psc\Data\Type\EnclosingType;
use Psc\Data\Type\ArrayType;
use Psc\Data\Type\ObjectType;
use Psc\Data\Type\MixedType;
use Psc\Data\Type\IntegerType;
use Psc\Data\Type\BooleanType;
use Psc\Data\Type\StringType;
use Psc\Data\Type\FloatType;
use Psc\Data\Type\Inferrer AS TypeInferrer;
use stdClass;
use Psc\Code\Code;

/**
 * 
 */
class Walker extends \Psc\SimpleObject {
  
  /**
   * @var Psc\Code\AST\CodeWriter
   */
  protected $codeWriter;

  /**
   * @var Psc\Data\Type\Inferrer
   */
  protected $typeInferrer;
  
  public function __construct(CodeWriter $codeWriter, TypeInferrer $typeInferrer = NULL) {
    $this->setCodeWriter($codeWriter);
    $this->typeInferrer = $typeInferrer ?: new TypeInferrer;
  }
  
  public function walkProgram(LProgram $program) {
    $code = NULL;
    foreach ($program->getStatements() as $statement) {
      $code .= $this->walkStatement($statement);
    }
    return $code;
  }
  
  public function walkStatement(LStatement $statement) {
    if ($statement instanceof LVariableDefinition) {
      $code = $this->walkVariableDefinition($statement);
    }
    
    return $this->codeWriter->writeStatement($code);
  }
  
  public function walkVariableDefinition(LVariableDefinition $variableDefinition) {
    return $this->codeWriter->writeVariableDefinition(
      $this->walkVariable($variable = $variableDefinition->getVariable()),
      $variableDefinition->hasValue()
        ? $this->walkValue($variableDefinition->getInitializer(), $variable->getType()->unwrap())
        : $this->walkExpression($variableDefinition->getInitializer(), 'variableDefinition')
    );
  }
  
  public function walkValue(LValue $value, Type $type = NULL) {
    $type = $type ?: $value->getInferredType()->unwrap();
    $phpValue = $value->unwrap();
    
    if ($type instanceof ArrayType) {
      return $this->walkArrayValue($phpValue, $type);
    } elseif ($this->isHashMapType($type)) {
      return $this->walkHashMapValue($phpValue, $type);
    } elseif ($type instanceof ObjectType) {
      return $this->walkObjectValue($phpValue, $type);
    }
    
    return $this->codeWriter->writeValue($value->unwrap(), $type);
  }
  
  protected function isHashMapType(Type $type) {
    return $type instanceof ObjectType && (!$type->hasClass() || $type->getClassFQN() === 'stdClass');
  }
  
  public function walkArrayValue($phpValue, Type $type) {
    $isList = $type->isList() !== NULL ? $type->isList() : \Psc\A::isNumeric($phpValue);
    
    if ($isList) {
      return $this->walkListValue((array) $phpValue, $type);
    } else {
      return $this->walkHashMapValue((object) $phpValue, $type); // alternativ: doch lValue übergeben und lValue->castToHashMap() oder sowas haben
    }
  }
  
  public function walkHashMapValue(stdClass $hashMap, Type $type) {
    $walkedHashMap = new stdClass;
    foreach ($hashMap as $field => $value) {
      $walkedHashMap->$field = $this->walkPHPValue($value, $type instanceof EnclosingType && $type->isTyped() ? $type->getType() : NULL, 'hashMap');
    }
    return $this->codeWriter->writeHashMap($walkedHashMap, $type);
  }
  
  public function walkListValue(Array $list, Type $type) {
    $walkedList = array();
    foreach ($list as $value) {
      $walkedList[] = $this->walkPHPValue($value, $type instanceof EnclosingType && $type->isTyped() ? $type->getType() : NULL, 'list');
    }
    return $this->codeWriter->writeList($walkedList, $type);
  }

  // das klappt schon ganz gut im Walker und brauchen wir für den fall,
  // dass wir z.b. ein joosesnippt innerhalb von einem array innerhalb eines anderen joosesnippt haben (siehe calender => calenderEvents für ein BEispiel)
  // problem hierbei: das "use" geht verloren, weil das obere Joosesnippet eben nicht bei $value instanceof Snippet in den Parametern was findet
  // viel besser wäre deshalb ein weiteres interface der expression, wo die Expression dann sagen kann
  // ich bin javascript und ich brauche die und die klasse (quasi: dependency expression) oder sowas
  // dann müsste der walker ein event triggern: found using class und dies könnte das joose snippet dann abfangen
  public function walkPHPValue($phpValue, Type $type = NULL, $context = NULL) {
    $type = $type ?: $this->typeInferrer->inferType($phpValue);
    
    if ($type instanceof ArrayType) {
      return $this->walkArrayValue($phpValue, $type);
    } elseif ($this->isHashMapType($type)) {
      return $this->walkHashMapValue($phpValue, $type);
    } elseif ($phpValue instanceof Walkable) {
      return $this->walkElement($phpValue->getWalkableAST(), $context);
    } elseif ($type instanceof ObjectType) {
      return $this->walkObjectValue($phpValue, $type);
    } elseif ($phpValue === NULL && $type instanceof MixedType) {
      return $this->codeWriter->writeValue($phpValue, $type);
    } elseif ($phpValue === NULL && $type instanceof MixedType) {
      return $this->codeWriter->writeValue($phpValue, $type);
    } elseif ($type instanceof IntegerType ||
              $type instanceof BooleanType ||
              $type instanceof StringType ||
              $type instanceof FloatType) {
      return $this->codeWriter->writeValue($phpValue, $type);
    }

    throw new \Psc\Code\NotImplementedException('Unbekannte phpValue: '.Code::varInfo($phpValue).' vom Walker nicht implementiert.');
  }
  
  /**
   * Ein Objekt welches keine HashMap ist
   *
   * zu dieser Funktion gelangen wir z.B. über verschachtelte Arrays oder Objekte aus PHP Konstrukten
   * es kann sein, dass wir hier wieder in die "normalen" Walks zurückkehren wollen, wenn das Objekt ein L-Element ist
   */
  public function walkObjectValue($classObject, Type $type) {
    if ($classObject instanceof Element) {
      return $this->walkElement($classObject, 'objectValue');
    }
    
    throw new \Psc\Code\NotImplementedException('Complex Objects in Arrays oder Objects sind noch nicht vom Walker implementiert. '.Code::varInfo($classObject));
  }
  
  public function walkElement(Element $element, $context = NULL) {
    if ($element instanceof LStatement) {
      return $this->walkStatement($element);
    } elseif ($element instanceof LExpression) {
      return $this->walkExpression($element, $context);
    } elseif ($element instanceof LProgram) {
      return $this->walkProgram($element);
    } else {
      throw new \RuntimeException('Element: '.Code::getClass($element).' hat keinen Walk definiert in walkElement');
    }
  }
  
  public function walkExpression(LExpression $expression, $context) {
    if ($expression instanceof LConstructExpression) {
      return $this->walkConstructExpression($expression);
    } else {
      return $this->codeWriter->writeExpression($expression->unwrap());
    }
  }
  
  public function walkConstructExpression(LConstructExpression $expression) {
    return $this->codeWriter->writeConstructExpression(
      $this->walkClassName($expression->getClass()),
      $this->walkArguments($expression->getArguments(), 'construct')
    );
  }
  
  public function walkArguments(LArguments $arguments, $context) {
    $walkedArguments = array();
    foreach ($arguments as $argument) {
      $walkedArguments[] = $this->walkArgument($argument, $context);
    }
    return $this->codeWriter->writeArguments($walkedArguments);
  }
  
  public function walkArgument(LArgument $argument, $context) {
    return $this->codeWriter->writeArgument(
      $this->walkValueOrExpression($argument->getBinding(), $context)
    );
  }
  
  protected function walkValueOrExpression($item, $context) {
    if ($item instanceof LValue) {
      return $this->walkValue($item);
    }
    if ($item instanceof LExpression) {
      return $this->walkExpression($item, $context);
    }
    
    throw new \RuntimeException('Der Type '.Code::varInfo($item). ' war nicht erwratet im branch ValueOrExpression');
  }
  
  public function walkClassName(LClassName $class) {
    return $this->codeWriter->writeClassName($class->getValue()->unwrap());
  }
  
  public function walkVariable(LVariable $variable) {
    return $this->codeWriter->writeVariable($variable->getName());
  }
  
  /**
   * @param Psc\Code\AST\CodeWriter $codeWriter
   */
  public function setCodeWriter(CodeWriter $codeWriter) {
    $this->codeWriter = $codeWriter;
    return $this;
  }
  
  /**
   * @return Psc\Code\AST\CodeWriter
   */
  public function getCodeWriter() {
    return $this->codeWriter;
  }
}
?>