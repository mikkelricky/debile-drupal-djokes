/* global fetch */
import './styles/app.scss'

import React, { useEffect, useState } from 'react'
import ReactDOM from 'react-dom'
import { Badge, Button, Card, Col, Form, ProgressBar, Row } from 'react-bootstrap'

const el = document.getElementById('app')
const options = JSON.parse(el.dataset.options || '{}')

const Djokes = ({ djokes_data_url: dataUrl, total_number_of_items: totalNumberOfItems }) => {
  const [error, setError] = useState(null)
  const [isLoaded, setIsLoaded] = useState(false)
  const [items, setItems] = useState([])
  const [index, setIndex] = useState(-1)

  const fetchItems = (url, currentItems = []) => {
    console.log(url, currentItems.length)
    fetch(url)
      .then(res => res.json())
      .then(
        (result) => {
          currentItems = currentItems.concat(result.data)
          setItems(currentItems)
          const nextUrl = result.links?.next?.href
          if (nextUrl) {
            fetchItems(nextUrl, currentItems)
          } else {
            // Wait a little before showing djokes.
            setTimeout(() => {
              setIsLoaded(true)
              setIndex(0)
              setItems(currentItems)
            }, 1000)
          }
        },
        // Note: it's important to handle errors here
        // instead of a catch() block so that we don't swallow
        // exceptions from actual bugs in components.
        (error) => {
          setIsLoaded(true)
          setError(error)
        }
      )
  }

  // Note: the empty deps array [] means
  // this useEffect will run once
  // similar to componentDidMount()
  useEffect(() => {
    fetchItems(dataUrl)
  }, [])

  const prev = () => {
    setIndex(index - 1 < 0 ? items.length - 1 : index - 1)
  }

  const next = () => {
    setIndex(index + 1 < items.length ? index + 1 : 0)
  }

  if (error) {
    return <div>Error: {error.message}</div>
  } else if (!isLoaded) {
    return (
      <div>
        <ProgressBar animated now={100 * items.length / totalNumberOfItems} />
        Loading djokes …
      </div>
    )
  } else {
    return (
      <>

        <Row>
          <Col>
            <Button onClick={prev} disabled={index === 0}>Previous djoke</Button>
          </Col>
          <Col className='text-center'>
            <Form.Control type='search' placeholder='Search' />
          </Col>
          <Col className='text-right'>
            <Button onClick={next} disabled={items.length - 1 === index}>Next djoke</Button>
          </Col>
        </Row>

        {items[index] &&
          <Card>
            <Card.Body>
              <Badge variant='primary'>
                {index + 1}/{items.length}
              </Badge>
              <h5 className='card-title'>{items[index].attributes.djoke}</h5>
              <p className='card-text text-right punchline'>{items[index].attributes.punchline}</p>
            </Card.Body>
          </Card>}

      </>
    )
  }
}

const App = (options) => {
  return (
    <>
      <Djokes {...options} />
    </>
  )
}

ReactDOM.render(
  <App {...options} />,
  el
)
