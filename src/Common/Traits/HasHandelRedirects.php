<?php

namespace VendorName\Skeleton\Common\Traits;

trait HasHandelRedirects
{
    protected $success_url;

    protected $fail_url;

    public function setSuccessUrl($url): self
    {
        $this->success_url = $url;
        $this->convertSuccessToFullUrl();

        return $this;
    }

    public function setFailUrl($url): self
    {
        $this->fail_url = $url;
        $this->convertFailToFullUrl();

        return $this;
    }

    public function handelRedirects()
    {
        $this->success_url = config('payment-:package_slug.success_url');
        $this->fail_url = config('payment-:package_slug.fail_url');

        $this->convertSuccessToFullUrl();
        $this->convertFailToFullUrl();
    }

    private function convertSuccessToFullUrl()
    {
        if ($this->success_url && ! filter_var($this->success_url, FILTER_VALIDATE_URL)) {
            $this->success_url = url($this->success_url);
        }
    }

    private function convertFailToFullUrl()
    {
        if ($this->fail_url && ! filter_var($this->fail_url, FILTER_VALIDATE_URL)) {
            $this->fail_url = url($this->fail_url);
        }
    }
}
