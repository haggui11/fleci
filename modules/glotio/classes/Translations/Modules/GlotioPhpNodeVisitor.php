<?php

interface GlotioPhpNodeVisitor
{
    const OBJECT_MODEL_REGEX = '/\bObjectModel(Core)?\b/i';

    /**
     * @return GlotioParsedClassModel[]
     */
    public function getFoundClassModels();
}
