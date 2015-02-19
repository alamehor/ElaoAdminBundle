<?php

/*
 * This file is part of the ElaoAdminBundle.
 *
 * (c) 2014 Elao <contact@elao.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elao\Bundle\AdminBundle\Action\REST;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The default action for read pages
 */
class ReadAction extends Action
{
    /**
     * {@inheritdoc}
     */
    public function getResponse(Request $request)
    {
        $format = $this->getFormat($request);
        $model  = $this->getModel($request);

        return $this->createResponse($this->getViewParameters($request, $model), 200, $format);
    }

    /**
     * Get view parameters
     *
     * @param Request $request
     * @param mixed $model
     *
     * @return array
     */
    protected function getViewParameters(Request $request, $model)
    {
        return ['model' => $model];
    }

    /**
     * Get model from request
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function getModel(Request $request)
    {
        $model = $this->modelManager->find($request->get('_route_params'));

        if (!$model) {
            throw new NotFoundHttpException;
        }

        return $model;
    }
}