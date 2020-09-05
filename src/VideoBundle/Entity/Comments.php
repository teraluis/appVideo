<?php

namespace VideoBundle\Entity;

/**
 * Comments
 */
class Comments
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \VideoBundle\Entity\Videos
     */
    private $video;

    /**
     * @var \VideoBundle\Entity\Users
     */
    private $user;


    /**
     * Set body
     *
     * @param string $body
     *
     * @return Comments
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Comments
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set video
     *
     * @param \VideoBundle\Entity\Videos $video
     *
     * @return Comments
     */
    public function setVideo(\VideoBundle\Entity\Videos $video = null)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return \VideoBundle\Entity\Videos
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set user
     *
     * @param \VideoBundle\Entity\Users $user
     *
     * @return Comments
     */
    public function setUser(\VideoBundle\Entity\Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \VideoBundle\Entity\Users
     */
    public function getUser()
    {
        return $this->user;
    }
}

