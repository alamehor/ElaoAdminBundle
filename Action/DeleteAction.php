<?php

namespace Elao\Bundle\MicroAdminBundle\Action;

use Symfony\Component\HttpFoundation\Request;
use Elao\Bundle\MicroAdminBundle\Behaviour\ActionInterface;

/**
 * Delete Action
 */
class DeleteAction implements ActionInterface
{
    public function getResponse(Request $request)
    {
        $model = $this->modelManager->find();
        $form  = $this->createForm($this->formType, $model);

        if ($form->handleRequest($request)->isSubmitted()) {

            if ($form->isValid()) {

                $this->modelManager->delete($model)->flush();

                return $this->redirect($this->redirection);
            }
        }

        return ['form' => $form->createView()];
    }
}