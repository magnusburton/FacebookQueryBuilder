<?php namespace SammyK\FacebookQueryBuilder;

class RootEdge extends Edge
{
    /**
     * Sets this as the root edge
     *
     * @var boolean
     */
    protected $is_root = true;

    /**
     * Compiled values that are ready to be concatenated
     *
     * @var array
     */
    protected $compiled_vales = [];

    /**
     * Compile the field values.
     *
     * @return void
     */
    public function compileFields()
    {
        if (count($this->fields) > 0)
        {
            $this->compiled_vales[] = 'fields=' . implode(',', $this->fields);
        }
    }

    /**
     * Compile the limit value.
     *
     * @return void
     */
    public function compileLimit()
    {
        if ($this->limit > 0)
        {
            $this->compiled_vales[] = 'limit=' . $this->limit;
        }
    }

    /**
     * Compile the final edge.
     *
     * @return string
     */
    public function compileEdge()
    {
        $this->compileLimit();
        $this->compileFields();

        $append = '';
        if (count($this->compiled_vales) > 0)
        {
            $append = '?' . implode('&', $this->compiled_vales);
        }

        //$append = '?' . http_build_query($values);
        return '/' . $this->name . $append;
    }
}
