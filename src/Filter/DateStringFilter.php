<?php


namespace App\Filter;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Service\Timezone;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;


class DateStringFilter extends AbstractContextAwareFilter
{

    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(ManagerRegistry $managerRegistry, ?RequestStack $requestStack = null, LoggerInterface $logger = null, array $properties = null, NameConverterInterface $nameConverter = null, Timezone $timezone)
    {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties, $nameConverter);
        $this->timezone = $timezone;
    }

    /**
     * Passes a property through the filter.
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }



        //logic of filter
        $rootAliases=$queryBuilder->getRootAliases()[0];
        foreach ($this->properties as $prop => $strategy) {
            try {
                if ($strategy==='after'){
                    $val =  $this->timezone->transformUserDateToAppTimezone( (new \DateTimeImmutable($value))->format('Y-m-d 00:00') ) ;
                }else if($strategy==='before'){
                    $val =  $this->timezone->transformUserDateToAppTimezone( (new \DateTimeImmutable($value))->format('Y-m-d 23:59') ) ;
                }elseif($strategy==='between'){
                    $dat_array=explode(',',$value);
                    if (count($dat_array)===2){
                        $after_between= $this->timezone->transformUserDateToAppTimezone( (new \DateTimeImmutable($dat_array[0]))->format('Y-m-d 00:00') );
                        $before_between=  $this->timezone->transformUserDateToAppTimezone( (new \DateTimeImmutable($dat_array[1]))->format('Y-m-d 23:59') );
                    }else{
                        throw new HttpException(400,'date between format is not correct');
                    }
                }else{
                    $date_after=  $this->timezone->transformUserDateToAppTimezone( (new \DateTimeImmutable($value))->format('Y-m-d 00:00') );
                    $date_before=  $this->timezone->transformUserDateToAppTimezone( (new \DateTimeImmutable($value))->format('Y-m-d 23:59') ) ;
                }

            } catch (\Exception $e) {
                // Silently ignore this filter if it can not be transformed to a \DateTime
                $this->logger->notice('Invalid filter ignored', [
                    'exception' => new InvalidArgumentException(sprintf('The field "%s" has a wrong date format. Use one accepted by the \DateTime constructor', $value))
                ]);

                return;
            }
          if ($prop === $property){
              switch ($strategy){
                  case 'after':

                      $queryBuilder->andwhere(sprintf("%s.%s >= '%s'",$rootAliases,$property,$val));
                      break;
                  case 'before':
                      $queryBuilder->andwhere(sprintf("%s.%s < '%s'",$rootAliases,$property,$val))
                          ->orWhere($queryBuilder->expr()->isNull(sprintf("%s.%s ",$rootAliases,$property)));
                      break;
                  case'between':
                      $queryBuilder->andwhere($queryBuilder->expr()->andX(sprintf("%s.%s < '%s'",$rootAliases,$property,$before_between),sprintf("%s.%s >= '%s'",$rootAliases,$property,$after_between)))
                          ->orWhere($queryBuilder->expr()->isNull(sprintf("%s.%s ",$rootAliases,$property)));;
                      break;
                  case 'exact':
                      $queryBuilder
                          ->andwhere(sprintf("%s.%s <= '%s'",$rootAliases,$property,$date_before))
                          ->andwhere(sprintf("%s.%s >= '%s'",$rootAliases,$property,$date_after))
                          ->orWhere($queryBuilder->expr()->isNull(sprintf("%s.%s ",$rootAliases,$property)));
                      break;

                  default:
                      throw new InvalidArgumentException(sprintf('%s as properties is invalid for %s',$strategy,$resourceClass));
              }
          }
        }






    }

    /**
     * Gets the description of this filter for the given resource.
     *
     * Returns an array with the filter parameter names as keys and array with the following data as values:
     *   - property: the property where the filter is applied
     *   - type: the type of the filter
     *   - required: if this filter is required
     *   - strategy: the used strategy
     *   - is_collection (optional): is this filter is collection
     *   - swagger (optional): additional parameters for the path operation,
     *     e.g. 'swagger' => [
     *       'description' => 'My Description',
     *       'name' => 'My Name',
     *       'type' => 'integer',
     *     ]
     *   - openapi (optional): additional parameters for the path operation in the version 3 spec,
     *     e.g. 'openapi' => [
     *       'description' => 'My Description',
     *       'name' => 'My Name',
     *       'schema' => [
     *          'type' => 'integer',
     *       ]
     *     ]
     * The description can contain additional data specific to a filter.
     *
     * @see \ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer::getFiltersParameters
     */
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }


        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $txt='';
            if ($strategy==='between'){
                $txt=':date delimeter is ,';
            }
            $description["regexp_$property"] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description'=>$strategy.' '.$txt,
                    'name' => $property,
                    'type' => 'a valid date format',
                ],
            ];
        }

        return $description;
    }
}
