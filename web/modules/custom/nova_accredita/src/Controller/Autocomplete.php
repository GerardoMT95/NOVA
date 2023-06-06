<?php
namespace Drupal\nova_accredita\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Element\EntityAutocomplete;

class Autocomplete extends ControllerBase {

    protected $taxoStrorage;

    public function __construct(EntityTypeManagerInterface $entity_type_manager){
        $this->taxoStrorage = $entity_type_manager->getStorage('taxonomy_term');
    }

    /**
     * @param ContainerInterface $container
     * @return CompanyImport|static
     */
    public static function create(ContainerInterface $container) {
        $form = new static(
            $container->get('entity_type.manager')
        );
        return $form;
    }


    /**
     * Handler for autocomplete request.
     */
    public function autocompleteComuni(Request $request) {
        $results = [];
        $input = $request->query->get('q');

        // Get the typed string from the URL, if it exists.
        if (!$input || strlen($input) < 3) {
            return new JsonResponse($results);
        }

        //recupero la regione dalla sessione
        $regione_corrente = $_SESSION['regione_corrente'];

//        if(!empty($regione_corrente) && is_numeric($regione_corrente)){
//            $result = \Drupal::entityQuery('taxonomy_term')
//                ->condition('name', "%" .$input .'%', 'LIKE')
//                ->condition('parent.target_id', $regione_corrente, '=')
//                ->condition('vid', 'regioni_comuni', '=')
//                ->execute();
//        }else{
        $query = \Drupal::entityQuery('taxonomy_term')
                ->condition('name', "" .$input .'%', 'LIKE');
        $group = $query->andConditionGroup()
            ->condition('vid', 'regioni_comuni', '=')
        ;
        $query->condition($group);
        $result = $query->execute();
//        }



        $terms = $this->taxoStrorage->loadMultiple($result);

        $input = Xss::filter($input);
        foreach ($terms as $term) {
//            switch ($node->isPublished()) {
//                case TRUE:
//                    $availability = '✅';
//                    break;
//
//                case FALSE:
//                default:
//                    $availability = '🚫';
//                    break;
//            }

            $label = [
                $term->getName(),
                $term->id(),
                '<small>(' . $term->id() . ')</small>',
//                $availability,
            ];

            $results[] = [
//                'value' => EntityAutocomplete::getEntityLabels([$term]),
//                'label' => implode(' ', $label),
                'value' => $label[0],
                'label' => $label[0],
            ];
        }

        return new JsonResponse($results);
    }

}